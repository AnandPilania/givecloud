<?php

namespace Database\Stories;

use Ds\Enums\RecurringFrequency;
use Ds\Models\RecurringPaymentProfile;
use Faker\Generator as Faker;

/**
 * @method \Ds\Models\RecurringPaymentProfile|array<RecurringPaymentProfile> create()
 */
class RecurringContributionStory extends ContributionStory
{
    /** @var \Ds\Domain\Commerce\Money */
    protected $amount;

    /** @var string */
    protected $recurringFrequency = RecurringFrequency::MONTHLY;

    /** @var int */
    protected $billingCycleAnchor = 1;

    /** @var bool */
    protected $includingInitialCharge = false;

    /** @var int */
    protected $payments = 0;

    public function __construct()
    {
        $this->amount = money(app(Faker::class)->randomFloat(2, 0.01, 250));
    }

    protected function charging(?float $amount = null, ?string $currencyCode = null, string $recurringFrequency = RecurringFrequency::MONTHLY): self
    {
        if ($amount !== null) {
            $this->amount = money($amount, $currencyCode);
        }

        $this->recurringFrequency = $recurringFrequency;

        return $this;
    }

    public function chargingAnnually(float $amount = null, string $currencyCode = null): self
    {
        return $this->charging($amount, $currencyCode, RecurringFrequency::ANNUALLY);
    }

    public function chargingMonthly(float $amount = null, string $currencyCode = null): self
    {
        return $this->charging($amount, $currencyCode, RecurringFrequency::MONTHLY);
    }

    public function includingInitialCharge(bool $includingInitialCharge = true): self
    {
        $this->includingInitialCharge = $includingInitialCharge;

        return $this;
    }

    public function includingPayments(int $payments = 3): self
    {
        $this->payments = $payments;

        return $this;
    }

    protected function execute(): RecurringPaymentProfile
    {
        $this->setUpContributionDateTestNow();

        // If our story includes a history of recurring payments then we should
        // travel back through the wormhole for the initial contribution
        if ($this->payments) {
            $this->travelBackInTimeForPayments();
        }

        $billingCycleAnchorKey = ($this->recurringFrequency === 'weekly' || $this->recurringFrequency === 'biweekly')
            ? 'recurring_day_of_week'
            : 'recurring_day';

        $product = ProductStory::factory()
            ->setupForRecurringDonations($this->recurringFrequency)
            ->when($this->forDpMembership, fn (ProductStory $story, $dpId) => $story->setupForDpMembership($dpId))
            ->create();

        if ($this->fromFundraisingPage) {
            $fundraisingPage = FundraisingPageStory::factory()
                ->forProductInstance($product)
                ->create();
        }

        $this->items['recurring_contribution'] = [
            'variant_id' => $product->defaultVariant->getKey(),
            'amt' => $this->amount->getAmount(),
            'recurring_frequency' => $this->recurringFrequency,
            $billingCycleAnchorKey => $this->billingCycleAnchor,
            'recurring_with_initial_charge' => $this->includingInitialCharge,
            'fundraising_page_id' => $fundraisingPage->id ?? null,
            'fundraising_member_id' => $fundraisingPage->member_organizer_id ?? null,
        ];

        $contribution = $this->makeContribution();
        $recurringPaymentProfile = $contribution->items[0]->recurringPaymentProfile;

        if ($this->payments) {
            $this->createPaymentHistory($recurringPaymentProfile);
        }

        $this->tearDownContributionDateTestNow();

        return $recurringPaymentProfile;
    }

    private function createPaymentHistory(RecurringPaymentProfile $recurringPaymentProfile): void
    {
        for ($i = 0; $i < $this->payments; $i++) {
            $this->travelTo($recurringPaymentProfile->next_billing_date);

            $recurringPaymentProfile->manualCharge($recurringPaymentProfile->paymentMethod);
            $recurringPaymentProfile->refresh();
        }
    }

    private function travelBackInTimeForPayments(): void
    {
        $recurringFrequencyMapping = [
            RecurringFrequency::WEEKLY => 'week',
            RecurringFrequency::BIWEEKLY => 'week',
            RecurringFrequency::MONTHLY => 'month',
            RecurringFrequency::QUARTERLY => 'quarter',
            RecurringFrequency::BIANNUALLY => 'month',
            RecurringFrequency::ANNUALLY => 'year',
        ];

        $numberOfUnits = $this->payments;

        if ($this->includingInitialCharge) {
            $numberOfUnits++;
        }

        if ($this->recurringFrequency === RecurringFrequency::BIWEEKLY) {
            $numberOfUnits *= 2;
        }

        if ($this->recurringFrequency === RecurringFrequency::BIANNUALLY) {
            $numberOfUnits /= 2;
        }

        $this->travelTo(fromLocal($this->contributionDate ?? 'now')->settings(['monthOverflow' => false])->sub(
            $recurringFrequencyMapping[$this->recurringFrequency],
            $numberOfUnits,
        ));
    }
}
