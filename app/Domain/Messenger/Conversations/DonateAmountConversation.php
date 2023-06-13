<?php

namespace Ds\Domain\Messenger\Conversations;

use BotMan\BotMan\Messages\Incoming\Answer;
use DomainException;
use Ds\Common\DataAccess;
use Ds\Domain\Messenger\Conversation;
use Ds\Domain\Shared\Exceptions\DisclosableException;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Order;
use Ds\Models\Product;
use Ds\Models\Variant;
use Ds\Services\DonorCoversCostsService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class DonateAmountConversation extends Conversation
{
    /** @var string */
    protected $donationType = 'onetime';

    /** @var int */
    protected $paymentDay;

    /** @var string|null */
    protected $coverTheFees = null;

    /** @var string */
    protected $defaultErrorMessage = "Oh shoot! We couldn't process the payment. Don't worry, nothing was charged.";

    /**
     * Start the conversation
     */
    public function handle()
    {
        $this->say('Thank you for using Text-to-Give!');

        $this->requireVerifiedAccountInGoodStanding();

        $this->parameters['amount'] = numeral($this->parameters['amount'] ?? null)->toFloat();

        if (empty($this->parameters['amount'])) {
            $this->askForDonationAmount();
        } else {
            $this->askForMonthlyDonation();
        }
    }

    /**
     * Ask for an amount.
     */
    public function askForDonationAmount()
    {
        $question = "Sorry, we didn't quite get that. How much would you like to donate (ex. $25) ?";

        $this->ask($question, function (Answer $answer) {
            $this->parameters['amount'] = numeral($answer->getText())->toFloat();

            if (empty($this->parameters['amount'])) {
                $this->askForDonationAmount();
            } else {
                $this->askForMonthlyDonation();
            }
        });
    }

    /**
     * Ask for a monthly donation.
     */
    public function askForMonthlyDonation()
    {
        if (! $this->getConversation()->metadata('enable_monthly', true)) {
            return $this->askCoverTheFees();
        }

        try {
            $product = $this->getProduct();
        } catch (ModelNotFoundException $e) {
            return $this->say($this->modelNotFoundErrorMessage);
        }

        if (! $product->variants->firstWhere('billing_period', 'monthly')) {
            return $this->askCoverTheFees();
        }

        $this->askYesOrNo(
            'Would you like to make this a monthly donation? Y or N',
            function () {
                $this->donationType = 'monthly';
                $this->askAboutPaymentDays();
            },
            function () {
                $this->donationType = 'onetime';
                $this->askCoverTheFees();
            }
        );
    }

    /**
     * Ask about the payment days.
     */
    public function askAboutPaymentDays()
    {
        try {
            $variant = $this->getVariant();
            $schedule = $variant->product->recurring_type ?? sys_get('rpp_default_type');
        } catch (ModelNotFoundException $e) {
            return $this->say($this->modelNotFoundErrorMessage);
        }

        if ($schedule === 'natural') {
            $this->paymentDay = 1;

            return $this->askCoverTheFees();
        }

        $paymentDays = sys_get('list:payment_day_options');

        if (count($paymentDays) === 1) {
            $this->paymentDay = (int) $paymentDays[0];

            return $this->askCoverTheFees();
        }

        $paymentDays = array_merge($paymentDays, $paymentDayOpts = array_map('number_suffix', $paymentDays));
        $lastPaymentDay = array_pop($paymentDayOpts);
        $paymentDayOpts = implode(', ', $paymentDayOpts) . ' or ' . $lastPaymentDay;

        $question = 'Which day of the month is best? ' . $paymentDayOpts;
        $this->ask($question, [
            [
                'pattern' => '(' . implode('|', $paymentDays) . ')',
                'callback' => function (Answer $answer) {
                    $this->paymentDay = (int) $answer->getText();
                    $this->askCoverTheFees();
                },
            ], [
                'pattern' => '(.*)',
                'callback' => function () use ($question) {
                    if (is_string($question)) {
                        $this->repeat("Sorry, we didn't quite get that. $question");
                    } else {
                        $this->repeat();
                    }
                },
            ],
        ]);
    }

    /**
     * Ask if they want to cover the fees.
     */
    public function askCoverTheFees()
    {
        if (! sys_get('dcc_enabled')) {
            return $this->completeDonation();
        }

        if (! $this->getConversation()->metadata('cover_the_fees', true)) {
            return $this->completeDonation();
        }

        try {
            $product = $this->getProduct();
        } catch (ModelNotFoundException $e) {
            return $this->say($this->modelNotFoundErrorMessage);
        }

        if (! $product->is_dcc_enabled) {
            return $this->completeDonation();
        }

        if (sys_get('dcc_ai_is_enabled')) {
            return $this->askCoverTheFeesWithDccAiPlus();
        }

        return $this->askCoverTheFeesWithoutDccAiPlus();
    }

    protected function askCoverTheFeesWithoutDccAiPlus()
    {
        $cost = (float) sys_get('dcc_cost_per_order');
        $rate = (float) sys_get('dcc_percentage');
        $fees = round($cost + ($this->parameters['amount'] * $rate / 100), 2);
        $amount = money($fees);

        $this->askYesOrNo(
            "Would you like to top up your donation by adding $amount to cover the processing fees? Y or N",
            function () {
                $this->coverTheFees = 'more_costs';
                $this->completeDonation();
            },
            function () {
                $this->completeDonation();
            }
        );
    }

    protected function askCoverTheFeesWithDccAiPlus()
    {
        $this->say('Help eliminate processing fees and the cost of managing your payment so we can maximize your impact.');

        $costs = app(DonorCoversCostsService::class)->getCosts((float) $this->parameters['amount']);
        $costs = array_map(fn ($amount) => money($amount), $costs);

        $question = <<<QUESTION
            Reply:
            1 for Most Costs {$costs['most_costs']},
            2 for More Costs {$costs['more_costs']},
            3 for Minimal Costs {$costs['minimum_costs']}, or
            N for No Costs.
            QUESTION;

        $this->ask($question, [
            [
                'pattern' => '(1|2|3|n)',
                'callback' => function (Answer $answer) {
                    switch ($answer->getText()) {
                        case '1': $this->coverTheFees = 'most_costs'; break;
                        case '2': $this->coverTheFees = 'more_costs'; break;
                        case '3': $this->coverTheFees = 'minimum_costs'; break;
                        default: $this->coverTheFees = null; break;
                    }

                    $this->completeDonation();
                },
            ], [
                'pattern' => '(.*)',
                'callback' => function () {
                    $this->repeat();
                },
            ],
        ]);
    }

    /**
     * Get the product to use for the donation.
     *
     * @return \Ds\Models\Product
     */
    public function getProduct(): Product
    {
        return Product::findOrFail($this->getConversation()->metadata['product']);
    }

    /**
     * Get the variant to use for the donation.
     *
     * @return \Ds\Models\Variant
     */
    public function getVariant(): Variant
    {
        $variant = $this->getProduct()->variants->firstWhere('billing_period', $this->donationType);

        if ($variant && $variant->metadata['redirects_to']) {
            $product = Product::findOrFail($variant->metadata['redirects_to']);
            $variant = $product->variants->firstWhere('billing_period', $this->donationType);
        }

        if (! $variant) {
            throw (new ModelNotFoundException)->setModel(Variant::class);
        }

        return $variant;
    }

    /**
     * Complete the donation.
     */
    public function completeDonation()
    {
        try {
            $variant = $this->getVariant();
        } catch (ModelNotFoundException $e) {
            return $this->say($this->modelNotFoundErrorMessage);
        }

        $item = [
            'variant_id' => $variant->id,
            'amt' => $this->parameters['amount'],
            'qty' => 1,
        ];

        if ($this->donationType !== 'onetime') {
            $item['recurring_frequency'] = $this->donationType;
            $item['recurring_day'] = $this->paymentDay;
            $item['recurring_with_initial_charge'] = true;
        }

        try {
            $cart = $this->createCart();

            if ($this->coverTheFees) {
                $cart->dcc_enabled_by_customer = (bool) $this->coverTheFees;
                $cart->dcc_type = sys_get('dcc_ai_is_enabled') ? $this->coverTheFees : null;
                $cart->save();
            }

            $this->addItemToCart($cart, $item);
            $this->completeCart($cart);

            $this->onCartCompeted($cart);
        } catch (DisclosableException $e) {
            return $this->say($e->getMessage());
        } catch (DomainException $e) {
            return $this->say($e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return $this->say($this->defaultErrorMessage);
        }
    }

    /**
     * Add an item to the cart.
     *
     * @param \Ds\Models\Order $cart
     * @param array $item
     */
    protected function addItemToCart(Order $cart, array $item)
    {
        if (empty($item['amt'])) {
            throw new MessageException(sprintf(
                'Oops! Are you sure you meant to donate %s? Please try again with a donation amount. (ex. %s)',
                money(0, $cart->currency)->format('$0'),
                money(25, $cart->currency)->format('$0'),
            ));
        }

        $cart->addItem($item);
    }

    /**
     * Fired when the cart is completed.
     *
     * @param \Ds\Models\Order $cart
     */
    protected function onCartCompeted(Order $cart)
    {
        $this->say(sprintf(
            "Thank you 🙂. A payment for %s has been processed and we've emailed you a confirmation.",
            money($cart->totalamount, $cart->currency)
        ));
    }

    /**
     * Get the configuration for the conversation.
     *
     * @return array
     */
    public static function configuration(): array
    {
        return [
            'label' => 'Donation with an amount',
            'example' => 'DONATE {amount}',
            'parameters' => [
                'amount' => static::AMOUNT_PARAMETER_REGEX,
            ],
            'settings' => DataAccess::collection([
                [
                    'type' => 'header',
                    'content' => 'Options',
                ], [
                    'type' => 'product',
                    'name' => 'product',
                    'label' => 'Product',
                    'hint' => '<i class="fa fa-question-circle"></i> Incoming donations will be made using this product',
                    'is_donation' => true,
                ], [
                    'type' => 'on-off',
                    'name' => 'enable_monthly',
                    'label' => 'Monthly',
                    'hint' => 'Enables recurring monthly donations',
                    'default' => true,
                ], [
                    'type' => 'on-off',
                    'name' => 'cover_the_fees',
                    'label' => 'Cover the Fees',
                    'default' => true,
                ],
            ]),
        ];
    }
}
