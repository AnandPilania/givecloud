<?php

namespace Database\Stories;

use Closure;
use Database\Factories\PaymentMethodFactory;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Shared\DateTime;
use Ds\Eloquent\Story;
use Ds\Events\OrderWasCompleted;
use Ds\Models\Order as Contribution;
use Ds\Models\PaymentMethod;

abstract class ContributionStory extends Story
{
    /** @var bool */
    protected $abandoningDonation = false;

    /** @var bool */
    protected $coveringCosts = false;

    /** @var int|null */
    protected $forDpMembership = null;

    /** @var bool */
    protected $fromFundraisingPage = false;

    /** @var \Database\Factories\PaymentMethodFactory */
    protected $paymentMethodFactory;

    /** @var \Ds\Domain\Commerce\Models\PaymentProvider */
    protected $paymentProvider;

    /** @var array */
    protected $items = [];

    /** @var bool */
    protected $tokenizingPaymentMethod = false;

    /** @var array|null */
    protected $betweenDates = null;

    /** @var \DateTime|null */
    protected $contributionDate = null;

    /** @var \DateTime|null */
    protected $previousTestNow = null;

    /** @var bool */
    protected $withoutSupporter = false;

    /**
     * @return static
     */
    public function abandoningDonation(bool $abandoningDonation = true): self
    {
        $this->abandoningDonation = $abandoningDonation;

        return $this;
    }

    /**
     * @return static
     */
    public function coveringCosts(bool $coveringCosts = true): self
    {
        $this->coveringCosts = $coveringCosts;

        return $this;
    }

    /**
     * @return static
     */
    public function betweenDates($startDate = null, $endDate = null): self
    {
        if (empty($startDate)) {
            $this->betweenDates = null;

            return $this;
        }

        $this->betweenDates = [fromLocal($startDate), fromLocal($endDate ?? 'now')];

        return $this;
    }

    /**
     * @return static
     */
    public function withoutSupporter(bool $withoutSupporter = true): self
    {
        $this->withoutSupporter = $withoutSupporter;

        return $this;
    }

    /**
     * @return static
     */
    public function forDpMembership(?int $dpId = null): self
    {
        $this->forDpMembership = $dpId;

        return $this;
    }

    /**
     * @return static
     */
    public function fromFundraisingPage(): self
    {
        $this->fromFundraisingPage = true;

        return $this;
    }

    /**
     * @return static
     */
    public function tokenizingPaymentMethod(array $attributes = []): self
    {
        $this->tokenizingPaymentMethod = true;

        return $this->usingPaymentMethod(PaymentMethod::factory()->tokenizing()->state($attributes));
    }

    /**
     * @return static
     */
    public function usingBankAccount(): self
    {
        return $this->usingPaymentMethod(PaymentMethod::factory()->bankAccount());
    }

    /**
     * @return static
     */
    public function usingCreditCard(array $attributes = []): self
    {
        return $this->usingPaymentMethod(PaymentMethod::factory()->creditCard()->state($attributes));
    }

    /**
     * @return static
     */
    public function usingPaymentMethod(PaymentMethodFactory $paymentMethodFactory): self
    {
        $this->paymentMethodFactory = $paymentMethodFactory;

        return $this;
    }

    /**
     * @return static
     */
    public function usingPaymentProvider(PaymentProvider $paymentProvider): self
    {
        $this->paymentProvider = $paymentProvider;

        return $this;
    }

    protected function setUpContributionDateTestNow()
    {
        $this->previousTestNow = DateTime::hasTestNow() ? now() : null;

        if (isset($this->betweenDates)) {
            $a = $this->betweenDates[0]->getTimestamp();
            $b = $this->betweenDates[1]->getTimestamp();

            $this->contributionDate = fromLocal(mt_rand(min($a, $b), max($a, $b)));
        } else {
            $this->contributionDate = $this->previousTestNow;
        }

        DateTime::setTestNow($this->contributionDate);
    }

    protected function tearDownContributionDateTestNow(): void
    {
        if ($this->contributionDate) {
            DateTime::setTestNow($this->previousTestNow);
        }
    }

    protected function makeContribution(Closure $callback = null): Contribution
    {
        // if dcc_enabled is not enabled the observer will blank out the
        // dcc rate/amount columns on the contribution
        $coverCostsDisabled = $this->coveringCosts && ! sys_get('dcc_enabled');

        if ($coverCostsDisabled) {
            sys_set('dcc_enabled', true);
        }

        $contribution = Contribution::factory()
            ->state([
                'createddatetime' => now(),
                'ordered_at' => now(),
                'currency_code' => $this->amount->getCurrencyCode(),
                'dcc_enabled_by_customer' => $this->coveringCosts,
                'send_confirmation_emails' => false,
                'dp_sync_order' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ])->create();

        // restore the dcc_enabled setting to it's disabled state
        if ($coverCostsDisabled) {
            sys_set('dcc_enabled', false);
        }

        if (empty($this->withoutSupporter)) {
            $story = SupporterStory::factory()
                ->withPaymentMethod($this->paymentMethodFactory)
                ->usingPaymentProvider($this->paymentProvider);

            if (empty($this->tokenizingPaymentMethod)) {
                $contribution->payment_type = 'payment_method';
            }

            $supporter = $story->create();
            $contribution->payment_method_id = $supporter->paymentMethods[0]->getKey();
            $contribution->payment_provider_id = $supporter->paymentMethods[0]->payment_provider_id;

            $contribution->populateMember($supporter);
        }

        array_walk($this->items, fn ($item) => $contribution->addItem($item));

        if ($callback) {
            $callback($contribution);
        }

        if ($this->abandoningDonation) {
            return $contribution;
        }

        $transactionResponse = $contribution->totalamount
            ? $contribution->paymentMethod->charge($contribution->totalamount, $contribution->currency_code)
            : TransactionResponse::fromPaymentMethod($contribution->paymentMethod);

        $contribution->updateWithTransactionResponse($transactionResponse);

        $contribution->is_processed = true;
        $contribution->invoicenumber = $contribution->client_uuid;
        $contribution->confirmationdatetime = now();
        $contribution->save();

        $contribution->initializeRecurringPayments();

        // execute a limited subset of the event listeners for the
        // OrderWasCompleted event, leaving out unnecessary items
        collect([
            \Ds\Listeners\Order\FlagTestOrders::class,
            \Ds\Listeners\Order\StockAdjustments::class,
            \Ds\Listeners\Order\IncrementPromoCodes::class,
            \Ds\Listeners\Order\ApplyMemberships::class,
            \Ds\Listeners\Order\CreateSponsors::class,
            \Ds\Listeners\Order\CreateTributes::class,
            // \Ds\Listeners\Order\IssueTaxReciept::class,
            // \Ds\Listeners\Order\TaxCloudCapture::class,
            // \Ds\Listeners\Order\UpdateEmailOptIn::class,
            // \Ds\Listeners\Order\SendNotificationEmails::class,
            // \Ds\Listeners\Order\TrackInGoogleAnalytics::class,
            // \Ds\Listeners\Order\DonorPerfectSync::class,
            \Ds\Listeners\Member\CalculateLifetimeGiving::class,
            // \Ds\Domain\Webhook\Listeners\OrderCompleted::class,
            \Ds\Listeners\Order\ProductPurchases::class,
            \Ds\Listeners\Order\UpdateLedgerEntries::class,
            \Ds\Listeners\Supporter\UpdateLastPaymentDate::class,
            // \Ds\Domain\QuickStart\Listeners\PaymentOccurredListener::class,
        ])->each(fn ($listener) => app($listener)->handle(new OrderWasCompleted($contribution)));

        return $contribution;
    }
}
