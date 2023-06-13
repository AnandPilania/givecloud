<?php

namespace Ds\Http\Controllers\Settings\Billing;

use Ds\Common\Chargebee\BillingPlansService;
use Ds\Domain\MissionControl\MissionControlService;
use Ds\Domain\Shared\DateTime;
use Ds\Http\Controllers\Controller;
use Ds\Repositories\ChargebeeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Throwable;

class ChargebeeSubscriptionController extends Controller
{
    public function createCustomerCheckout(): JsonResponse
    {
        if (! user()->can('admin.billing')) {
            abort(404);
        }

        try {
            app('chargebee')->updateCustomer(site()->client->customer_id, [
                'preferred_currency_code' => app(BillingPlansService::class)->currency(),
            ]);

            $hostedPage = app('chargebee')->createCheckoutPageForPlan(
                request()->planId,
                site()->client->customer_id,
                route('billing.chargebee.callback')
            );
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($hostedPage->getValues());
    }

    public function callback(): RedirectResponse
    {
        if (request()->state !== 'succeeded' || request()->isNotFilled('id')) {
            $this->flash->error('An error occurred, please try again.');

            return redirect()->route('backend.settings.billing');
        }

        try {
            $page = app('chargebee')->hostedPage(request()->get('id'));

            $subscription = $page->content()->subscription();
            $customer = $page->content()->customer();
            $plan = app(BillingPlansService::class)->fromChargebeeId($subscription->planId);
        } catch (Throwable $e) {
            $this->flash->error($e->getMessage());

            return redirect()->route('backend.settings.billing');
        }

        app(MissionControlService::class)->updateClient([
            'address1' => $customer->billingAddress->line1 ?? null,
            'address2' => $customer->billingAddress->line2 ?? null,
            'city' => $customer->billingAddress->city ?? null,
            'postal_code' => $customer->billingAddress->zip ?? null,
            'province' => $customer->billingAddress->state ?? null,
            'country' => $customer->billingAddress->country ?? null,
            'tier' => $plan->missionControlPlanName(),
            'mrr' => $subscription->mrr > 0 ? $subscription->mrr / 100 : 0,
            'billing_interval' => $subscription->billingPeriodUnit === 'year' || ($subscription->billingPeriodUnit === 'month' && $subscription->billingPeriod === 12) ? 'A' : 'M',
            'ordered_on' => DateTime::createFromTimestamp($subscription->createdAt),
            'billing_start_on' => DateTime::createFromTimestamp($subscription->startedAt),
            'status' => 'ACTIVE',
            'reseller_id' => 1, // Set reseller to Givecloud
        ]);

        app(MissionControlService::class)->updateSite([
            'txn_fee' => $plan->transactionFees,
            'txn_fee_currency' => $subscription->currencyCode,
            'reseller_id' => 1, // Set reseller to Givecloud
        ]);

        app(MissionControlService::class)->setSubscription([
            'reseller_id' => 1,
            'client_id' => site()->client_id,
            'chargebee_subscription_id' => $subscription->id,
            'status' => 'active',
            'plan_id' => app(MissionControlService::class)->getPlans()->firstWhere('name', $plan->missionControlPlanName())->id ?? null,
            'amount' => $subscription->planAmount / 100,
            'currency' => $subscription->currencyCode,
            'transaction_fee' => $plan->transactionFees,
            'mrr' => $subscription->mrr > 0 ? $subscription->mrr / 100 : 0,
            'interval' => $subscription->billingPeriodUnit === 'year' || ($subscription->billingPeriodUnit === 'month' && $subscription->billingPeriod === 12) ? 'year' : 'month',
            'purchased_date' => DateTime::createFromTimestamp($subscription->createdAt),
            'billing_start_date' => DateTime::createFromTimestamp($subscription->startedAt),
            'next_billing_at' => DateTime::createFromTimestamp($subscription->nextBillingAt),
            'support_chat' => $plan->support,
            'support_phone' => 'none',
        ]);

        app(MissionControlService::class)->updateResellerId();

        $url = MissionControlService::getMissionControlApiUrl('hubspot/sync/' . site()->client_id);
        Http::withToken(config('services.missioncontrol.api_token'))->post($url);

        app(ChargebeeRepository::class)->flushCache();
        app(MissionControlService::class)->flushSiteCache();

        $this->flash->success("🎉 &nbsp; Yay! You're all set!");

        return redirect()->route('backend.settings.billing');
    }
}
