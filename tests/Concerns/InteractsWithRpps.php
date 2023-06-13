<?php

namespace Tests\Concerns;

use Closure;
use Ds\Domain\Shared\DateTime;
use Ds\Domain\Sponsorship\Models\PaymentOption;
use Ds\Domain\Sponsorship\Models\PaymentOptionGroup;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Enums\RecurringFrequency;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Events\OrderWasCompleted;
use Ds\Models\Member as Account;
use Ds\Models\Membership;
use Ds\Models\Order;
use Ds\Models\PaymentMethod;
use Ds\Models\Product;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\ShippingMethod;
use Ds\Models\Transaction;
use Ds\Models\Variant;
use Illuminate\Support\Collection;

trait InteractsWithRpps
{
    private function generateAccount(): Account
    {
        return $this->generateAccounts(1)->first();
    }

    private function generateAccounts(int $numberOfAccounts = 1): Collection
    {
        return Account::factory($numberOfAccounts)->individual()->create();
    }

    private function generateAccountWithPaymentMethods(int $numberOfPaymentMethods = 1): Account
    {
        return $this->generateAccountsWithPaymentMethods(1, $numberOfPaymentMethods)->first();
    }

    private function generateAccountsWithPaymentMethods(int $numberOfAccounts = 1, int $numberOfPaymentMethods = 1): Collection
    {
        return $this->generateAccounts($numberOfAccounts)->each(function (Account $account) use ($numberOfPaymentMethods) {
            $account->paymentMethods()->saveMany(
                PaymentMethod::factory($numberOfPaymentMethods)->for($account, 'member')->creditCard()->make()
            );
            $account->paymentMethods()->first()->useAsDefaultPaymentMethod();
        });
    }

    private function generateAccountsWithPaymentMethodsHavingInsufficientFunds(int $numberOfAccounts = 1, int $numberOfPaymentMethods = 1): Collection
    {
        return $this->generateAccounts($numberOfAccounts)->each(function ($account) use ($numberOfPaymentMethods) {
            $account->paymentMethods()->saveMany(
                PaymentMethod::factory($numberOfPaymentMethods)->creditCard()->insufficientFunds()->make()
            );

            $account->paymentMethods()->first()->useAsDefaultPaymentMethod();
        });
    }

    private function generateAccountsWithPMsAndRpps(int $numberOfAccounts = 1): Collection
    {
        return $this->generateAccountsWithPaymentMethods($numberOfAccounts)->each(function ($account) {
            $this->generateRpps($account, $account->defaultPaymentMethod, 1, 'USD');
        });
    }

    private function generateAccountsWithPMsHavingInsuffientFundsAndRpps(int $numberOfAccounts = 1): Collection
    {
        return $this->generateAccountsWithPaymentMethodsHavingInsufficientFunds($numberOfAccounts)->each(function ($account) {
            $this->generateRpps($account, $account->defaultPaymentMethod, 1, 'USD');
        });
    }

    private function generateRpp(
        Account $account,
        PaymentMethod $paymentMethod,
        int $numberOfItems = 1,
        ?string $currencyCode = null,
        ?Closure $addItemCallback = null,
        string $billingFrequency = 'monthly',
        int $billingCycleAnchor = 1
    ): RecurringPaymentProfile {
        return $this->generateRpps(
            $account,
            $paymentMethod,
            $numberOfItems,
            $currencyCode,
            $addItemCallback,
            $billingFrequency,
            $billingCycleAnchor
        )[0];
    }

    private function generateSuspendedRpps(
        Account $account,
        PaymentMethod $paymentMethod,
        int $numberOfItems = 1,
        ?string $currencyCode = null
    ): array {
        return collect($this->generateRpps($account, $paymentMethod, $numberOfItems, $currencyCode))->each(function ($rpp) {
            $rpp->status = RecurringPaymentProfileStatus::SUSPENDED;
            $rpp->save();
        })->all();
    }

    private function generateSponsorshipRpps(
        Account $account,
        PaymentMethod $paymentMethod,
        int $numberOfItems = 1,
        ?string $currencyCode = null
    ): array {
        return $this->generateRpps(
            $account,
            $paymentMethod,
            $numberOfItems,
            $currencyCode,
            function (Order $order) {
                $sponsorship = Sponsorship::factory()->has(
                    PaymentOptionGroup::factory()
                        ->has(PaymentOption::factory()->recurring(RecurringFrequency::MONTHLY), 'options')
                )->create();

                $order->addSponsorship([
                    'sponsorship_id' => $sponsorship->getKey(),
                    'payment_option_id' => $sponsorship->paymentOptionGroups[0]->options[0]->getKey(),
                    'initial_charge' => true,
                ]);
            }
        );
    }

    private function generateMembershipRpps(
        Account $account,
        PaymentMethod $paymentMethod,
        Membership $membership,
        int $numberOfItems = 1,
        ?string $currencyCode = null
    ): array {
        return $this->generateRpps(
            $account,
            $paymentMethod,
            $numberOfItems,
            $currencyCode,
            function (Order $order) use ($membership) {
                $product = Product::factory()->donation()->allowOutOfStock()->create();
                $variant = Variant::factory()->donation()->create();

                $variant->membership()->associate($membership);
                $product->variants()->save($variant);

                $order->addItem([
                    'variant_id' => $variant->getKey(),
                    'amt' => 10,
                    'recurring_frequency' => 'annually',
                    'recurring_day' => 1,
                ]);
            }
        );
    }

    private function generateRpps(
        Account $account,
        PaymentMethod $paymentMethod,
        int $numberOfItems = 1,
        ?string $currencyCode = null,
        ?Closure $addItemCallback = null,
        string $billingFrequency = 'monthly',
        int $billingCycleAnchor = 1
    ): array {
        $shouldSetTestNow = ! DateTime::hasTestNow();

        if ($shouldSetTestNow) {
            DateTime::setTestNow(fromLocal('today')->subMonthWithoutOverflow()->asUtc());
        }

        $order = Order::factory()->for($account, 'member')->create([
            'is_processed' => true,
            'invoicenumber' => function ($attributes) {
                return $attributes['client_uuid'];
            },
            'createddatetime' => now(),
            'ordered_at' => now(),
            'currency_code' => $currencyCode ?? sys_get('dpo_currency'),
            'payment_type' => 'payment_method',
            'payment_method_id' => $paymentMethod->getKey(),
            'payment_provider_id' => $paymentMethod->payment_provider_id,
            'send_confirmation_emails' => false,
            'dp_sync_order' => false,
        ]);

        $order->populateMember($account);

        if (empty($addItemCallback)) {
            $addItemCallback = function (Order $order) use ($billingFrequency, $billingCycleAnchor) {
                $product = Product::factory()->donation()->allowOutOfStock()->create();
                $variant = Variant::factory()->donation()->create();

                $product->variants()->save($variant);

                $billingCycleAnchorKey = ($billingFrequency === 'weekly' || $billingFrequency === 'biweekly')
                    ? 'recurring_day_of_week'
                    : 'recurring_day';

                $order->addItem([
                    'variant_id' => $variant->getKey(),
                    'amt' => 10,
                    'recurring_frequency' => $billingFrequency,
                    $billingCycleAnchorKey => $billingCycleAnchor,
                ]);
            };
        }

        for ($i = 0; $i < $numberOfItems; $i++) {
            $addItemCallback($order);
        }

        $order->confirmationdatetime = now();
        $order->save();

        $rpps = $order->initializeRecurringPayments();

        event(new OrderWasCompleted($order));

        if ($shouldSetTestNow) {
            DateTime::setTestNow();
        }

        return $rpps;
    }

    private function createTransactionsWithRPP(int $count = 1): Collection
    {
        $transactions = collect();

        for ($i = 1; $i <= $count; $i++) {
            $account = $this->generateAccountWithPaymentMethods();
            $rpp = $this->generateRpps($account, $account->defaultPaymentMethod)[0];
            $rpp->order->shipping_amount = $this->faker->randomFloat(2, 0.01, 5);
            $rpp->order->shipping_method_id = ShippingMethod::factory()->create()->getKey();
            $rpp->order->totalamount = round($rpp->order->subtotal + $rpp->order->taxtotal + $rpp->order->shipping_amount + $rpp->order->dcc_total_amount, 2);
            $rpp->order->save();
            $transactions->push(Transaction::factory()->paid()->for($rpp)->create());
        }

        return $transactions;
    }

    private function createTransactionWithRPP(): Transaction
    {
        return $this->createTransactionsWithRPP()->first();
    }
}
