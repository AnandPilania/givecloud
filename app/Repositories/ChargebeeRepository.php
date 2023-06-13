<?php

namespace Ds\Repositories;

use ChargeBee\ChargeBee\Models\Customer as ChargeBeeCustomer;
use ChargeBee\ChargeBee\Models\Plan as ChargeBeePlan;
use ChargeBee\ChargeBee\Models\Subscription as ChargeBeeSubscription;
use Ds\Common\Chargebee\BillingPlansService;
use Ds\Domain\Shared\DateTime;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;
use Throwable;

class ChargebeeRepository
{
    /** @var \Illuminate\Cache\TaggedCache */
    private $cache;

    /**
     * @param \Illuminate\Cache\CacheManager $cache
     */
    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache->tags('chargebee');
    }

    /**
     * Get the Chargebee Customer Id.
     *
     * @return string|null
     */
    public function getCustomerId(): ?string
    {
        return site()->client->customer_id;
    }

    /**
     * Get the Chargebee Customer.
     *
     * @return \ChargeBee\ChargeBee\Models\Customer|null
     */
    public function getCustomer(): ?ChargeBeeCustomer
    {
        return $this->cache->remember(
            'chargebee_3:customer',
            now()->addDays(7),
            function () {
                try {
                    return app('chargebee')->customer($this->getCustomerId());
                } catch (Throwable $e) {
                    return null;
                }
            }
        );
    }

    /**
     * Get the active Chargebee Subscription.
     *
     * @return \ChargeBee\ChargeBee\Models\Subscription|null
     */
    public function getSubscription(): ?ChargeBeeSubscription
    {
        return $this->cache->remember(
            'chargebee_3:subscription',
            now()->addDays(7),
            function () {
                try {
                    $subscriptions = app('chargebee')->subscriptions($this->getCustomerId());
                } catch (Throwable $e) {
                    return null;
                }

                if ($subscriptions) {
                    return $subscriptions->firstWhere('status', 'active');
                }

                return null;
            }
        );
    }

    /**
     * Get the active Chargebee Plan.
     *
     * @return \ChargeBee\ChargeBee\Models\Plan|null
     */
    public function getPlan(): ?ChargeBeePlan
    {
        return $this->cache->remember(
            'chargebee_3:plan',
            now()->addDays(7),
            function () {
                $subscription = $this->getSubscription();

                if (empty($subscription)) {
                    return null;
                }

                try {
                    return app('chargebee')->plan($subscription->planId);
                } catch (Throwable $e) {
                    return null;
                }
            }
        );
    }

    public function getPlans(): Collection
    {
        $planIds = app(BillingPlansService::class)->chargebeeIds();

        return cache()->store('app')->remember(
            'chargebee_3:plans:' . md5(serialize($planIds)),
            now()->addDays(7),
            function () use ($planIds) {
                return app('chargebee')->getPlans([
                    'id[in]' => '[' . implode(',', $planIds) . ']',
                ]);
            }
        );
    }

    /**
     * Get the Chargebee Invoices.
     *
     * @return \Illuminate\Support\Collection<\ChargeBee\ChargeBee\Models\Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->cache->remember(
            'chargebee_3:invoices',
            now()->addHours(12),
            function () {
                try {
                    return app('chargebee')->invoices($this->getCustomerId());
                } catch (Throwable $e) {
                    return collect();
                }
            }
        );
    }

    /**
     * Get the outstanding Chargebee Invoices balance.
     *
     * @return float
     */
    public function getBalance(): float
    {
        return $this->cache->remember(
            'chargebee_3:balance',
            now()->addHours(12),
            function () {
                try {
                    $invoices = $this->getInvoices()
                        ->map->getValues()
                        ->where('status', 'payment_due');
                } catch (Throwable $e) {
                    return 0.0;
                }

                return $invoices->sum('amount_due') / 100.0;
            }
        );
    }

    /**
     * Get the due date of the soonest Chargebee Invoice.
     *
     * @return \Ds\Domain\Shared\DateTime|null
     */
    public function getBalanceDueDate(): ?DateTime
    {
        return $this->cache->remember(
            'chargebee_3:balance_due_date',
            now()->addHours(12),
            function () {
                try {
                    $invoices = $this->getInvoices()
                        ->map->getValues()
                        ->where('status', 'payment_due');
                } catch (Throwable $e) {
                    return null;
                }

                if ($invoices->isNotEmpty()) {
                    return fromUtc($invoices->min('due_date'));
                }

                return null;
            }
        );
    }

    /**
     * Get valid Chargebee Payment Sources.
     *
     * @return \Illuminate\Support\Collection<\ChargeBee\ChargeBee\Models\PaymentSource>
     */
    public function getValidPaymentSources(): Collection
    {
        return $this->cache->remember(
            'chargebee_3:payment_sources',
            now()->addDays(7),
            function () {
                try {
                    return app('chargebee')->getValidPaymentSources($this->getCustomerId());
                } catch (Throwable $e) {
                    return collect();
                }
            }
        );
    }

    public function hasPastDueBalance(): bool
    {
        if ($this->getBalance() < 10) {
            return false;
        }

        $dueDate = $this->getBalanceDueDate();

        if ($dueDate === null) {
            return false;
        }

        return $dueDate->addDays(25)->isPast();
    }

    public function hasNoPastDueBalance(): bool
    {
        return ! $this->hasPastDueBalance();
    }

    /**
     * Is there a valid Chargebee Payment Source.
     *
     * @return bool
     */
    public function hasValidPaymentSource(): bool
    {
        return $this->getValidPaymentSources()->isNotEmpty();
    }

    /**
     * Get valid Chargebee Payment Source.
     *
     * @return object
     */
    public function getPaymentMethod(): ?object
    {
        $paymentSource = $this->getValidPaymentSources()->first();

        if (isset($paymentSource->bankAccount)) {
            return (object) [
                'brand' => $paymentSource->bankAccount->bankName,
                'masked' => "************{$paymentSource->bankAccount->last4}",
                'last4' => $paymentSource->bankAccount->last4,
            ];
        }

        if (isset($paymentSource->card)) {
            return (object) [
                'brand' => $paymentSource->card->brand,
                'masked' => $paymentSource->card->maskedNumber,
                'last4' => $paymentSource->card->last4,
            ];
        }

        if (isset($paymentSource->paypal)) {
            return (object) [
                'brand' => $paymentSource->paypal->email,
                'masked' => $paymentSource->paypal->agreementId ?? $paymentSource->paypal->email ?? '',
                'last4' => $paymentSource->paypal->agreementId ?? $paymentSource->paypal->email ?? '',
            ];
        }

        return null;
    }

    /**
     * Flush the Chargebee cache.
     *
     * @return bool
     */
    public function flushCache(): bool
    {
        return $this->cache->flush();
    }
}
