<?php

namespace Ds\Http\Controllers;

use Ds\Common\Chargebee\BillingPlansService;
use Ds\Domain\MissionControl\MissionControlService;
use Ds\Models\Product;

class ExpressSetupController extends Controller
{
    public function __invoke()
    {
        $location = rescueQuietly(fn () => app('geoip')->getLocationData(request()->ip()));

        sys_set([
            'onboarding_flow' => 0,
            'timezone' => data_get($location, 'timezone', 'America/New_York'),
            'timezone_confirmed' => 0,
            'feature_fundraising_forms' => 1,
        ]);

        $preferredCurrency = app(BillingPlansService::class)->currency();

        if ($preferredCurrency === 'CAD' && site()->client->customer_id) {
            app('chargebee')->updateCustomer(site()->client->customer_id, [
                'preferred_currency_code' => app(BillingPlansService::class)->currency(),
            ]);
        }

        if ($preferredCurrency === 'CAD') {
            app(MissionControlService::class)->updateSite([
                'txn_fee_currency' => $preferredCurrency,
            ]);
            app(MissionControlService::class)->updateSubscriptions([
                'currency' => $preferredCurrency,
            ]);
        }

        $defaultForm = Product::query()
            ->donationForms()
            ->first();

        if ($defaultForm) {
            $defaultForm->createddatetime = fromLocal('now')->toUtc();
            $defaultForm->createdbyuserid = auth()->user()->getAuthIdentifier();
            $defaultForm->save();

            return redirect()->route('backend.fundraising.forms.view', [
                'form' => $defaultForm->hashid,
            ]);
        }

        return redirect()->route('backend.session.index');
    }
}
