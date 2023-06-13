<?php

namespace Ds\Common\Chargebee;

use ChargeBee\ChargeBee\Models\Card as ChargeBeeCard;
use ChargeBee\ChargeBee\Models\Customer as ChargeBeeCustomer;
use ChargeBee\ChargeBee\Models\HostedPage as ChargeBeeHostedPage;
use ChargeBee\ChargeBee\Models\Invoice as ChargeBeeInvoice;
use ChargeBee\ChargeBee\Models\PaymentSource as ChargeBeePaymentSource;
use ChargeBee\ChargeBee\Models\Plan as ChargeBeePlan;
use ChargeBee\ChargeBee\Models\PortalSession as ChargeBeePortalSession;
use ChargeBee\ChargeBee\Models\Subscription as ChargeBeeSubscription;
use Illuminate\Support\Collection;

class Manager
{
    /** @var \Illuminate\Foundation\Application */
    protected $app;

    /**
     * Create an instance
     *
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Create a customer.
     *
     * @param array $data
     * @return \ChargeBee\ChargeBee\Models\Customer
     */
    public function createCustomer(array $data)
    {
        $res = ChargeBeeCustomer::create($data);

        return $res->customer();
    }

    public function updateCustomer(string $id, array $data): ChargeBeeCustomer
    {
        $res = ChargeBeeCustomer::update($id, $data);

        return $res->customer();
    }

    /**
     * Retrieve a plan.
     *
     * @param string $planId
     * @return \ChargeBee\ChargeBee\Models\Plan
     */
    public function getPlan($planId)
    {
        $res = ChargeBeePlan::retrieve($planId);

        return $res->plan();
    }

    public function getPlans(array $params = []): Collection
    {
        return collect(ChargeBeePlan::all($params))->map(function ($plan) {
            return $plan->plan();
        });
    }

    /**
     * Create a subscription.
     *
     * @param array $data
     * @return \ChargeBee\ChargeBee\Models\Subscription
     */
    public function createSubscription(array $data)
    {
        $res = ChargeBeeSubscription::create($data);

        $subscription = $res->subscription();
        $subscription->addons = collect($subscription->addons);
        $subscription->card = $res->card();

        return $subscription;
    }

    /**
     * Create a subscription.
     *
     * @param string $customerId
     * @param array $data
     * @return \ChargeBee\ChargeBee\Models\Subscription
     */
    public function createSubscriptionForCustomer($customerId, array $data)
    {
        $res = ChargeBeeSubscription::createForCustomer($customerId, $data);

        $subscription = $res->subscription();
        $subscription->addons = collect($subscription->addons);
        $subscription->card = $res->card();

        return $subscription;
    }

    /**
     * Change the plan associated with a subscription.
     *
     * @param string $subscriptionId
     * @param string $planId
     * @return \ChargeBee\ChargeBee\Models\Subscription
     */
    public function changeSubscriptionPlan($subscriptionId, $planId)
    {
        $res = ChargeBeeSubscription::update($subscriptionId, [
            'plan_id' => $planId,
        ]);

        return $res->subscription();
    }

    /**
     * Cancel a subscription.
     *
     * @param string $subscriptionId
     * @param bool $cancelImmediately
     * @return \ChargeBee\ChargeBee\Models\Subscription
     */
    public function cancelSubscription($subscriptionId, $cancelImmediately = false)
    {
        $res = ChargeBeeSubscription::cancel($subscriptionId, [
            'end_of_term' => ! $cancelImmediately,
        ]);

        return $res->subscription();
    }

    /**
     * Resume a subscription.
     *
     * @param string $subscriptionId
     * @return \ChargeBee\ChargeBee\Models\Subscription
     */
    public function resumeSubscription($subscriptionId)
    {
        $res = ChargeBeeSubscription::removeScheduledCancellation($subscriptionId);

        return $res->subscription();
    }

    /**
     * Reactivate a subscription.
     *
     * @param string $subscriptionId
     * @return \ChargeBee\ChargeBee\Models\Subscription
     */
    public function reactivateSubscription($subscriptionId)
    {
        $res = ChargeBeeSubscription::reactivate($subscriptionId);

        return $res->subscription();
    }

    /**
     * Invoices for a subscription.
     *
     * @param string $customerId
     * @return \Illuminate\Support\Collection<\ChargeBee\ChargeBee\Models\Invoice>
     */
    public function invoices($customerId)
    {
        $res = ChargeBeeInvoice::all([
            'customerId[is]' => $customerId,
        ]);

        return collect($res)->map->invoice();
    }

    /**
     * Create a stripe payment source using a temporary token.
     *
     * @param string $customerId
     * @param string $token
     * @return \ChargeBee\ChargeBee\Models\PaymentSource
     */
    public function createStripePaymentSource($customerId, $token)
    {
        $res = ChargeBeePaymentSource::createUsingTempToken([
            'customerId' => $customerId,
            'gatewayAccountId' => $this->app['config']->get('services.chargebee.gateway_account'),
            'type' => 'card',
            'tmpToken' => $token,
            'replacePrimaryPaymentSource' => true,
        ]);

        return $res->paymentSource();
    }

    /**
     * Get Payment Sources for Customer
     *
     * @param string $customerId
     * @return \Illuminate\Support\Collection<\ChargeBee\ChargeBee\Models\PaymentSource>
     */
    public function getValidPaymentSources($customerId)
    {
        $res = ChargeBeePaymentSource::all([
            'customerId[is]' => $customerId,
        ]);

        return collect($res)
            ->map(function ($source) {
                return $source->paymentSource();
            })->filter(function ($source) {
                return $source->status == 'valid';
            });
    }

    public function createCheckoutPageForPlan(string $planId, string $customerId, string $redirectUrl): ChargeBeeHostedPage
    {
        $res = ChargeBeeHostedPage::checkoutNew([
            'subscription' => [
                'planId' => $planId,
            ],
            'customer' => [
                'id' => $customerId,
            ],
            'redirectUrl' => $redirectUrl,
        ]);

        return $res->hostedPage();
    }

    /**
     * Create a stripe payment source using a temporary token.
     *
     * @param string $customerId
     * @param string $redirectUrl
     * @return \ChargeBee\ChargeBee\Models\PortalSession
     */
    public function createPortalSession($customerId, $redirectUrl)
    {
        $res = ChargeBeePortalSession::create([
            'customer' => ['id' => $customerId],
            'redirectUrl' => $redirectUrl,
        ]);

        return $res->portalSession();
    }

    /**
     * Get Customer from Chargebee
     *
     * @param string $customerId
     * @return array<\ChargeBee\ChargeBee\Models\Customer>
     */
    public function customer($customerId)
    {
        $res = ChargeBeeCustomer::retrieve($customerId);

        return $res->customer();
    }

    /**
     * Get cards for a Customer from Chargebee
     *
     * @param string $customerId
     * @return array<\ChargeBee\ChargeBee\Models\Card>
     */
    public function card($customerId)
    {
        $res = ChargeBeeCard::retrieve($customerId);

        return $res->card();
    }

    public function hostedPage(string $hostedPageId): ChargeBeeHostedPage
    {
        $res = ChargeBeeHostedPage::retrieve($hostedPageId);

        return $res->hostedPage();
    }

    /**
     * Get subscriptions Customer from Chargebee
     *
     * @param string $customerId
     * @return \Illuminate\Support\Collection<\ChargeBee\ChargeBee\Models\Subscription>
     */
    public function subscriptions($customerId)
    {
        $all = ChargeBeeSubscription::subscriptionsForCustomer($customerId);

        return collect($all)->map->subscription();
    }

    /**
     * Get a plan
     *
     * @param string $planId
     * @return array<\ChargeBee\ChargeBee\Models\Plan>
     */
    public function plan($planId)
    {
        $res = ChargeBeePlan::retrieve($planId);

        return $res->plan();
    }
}
