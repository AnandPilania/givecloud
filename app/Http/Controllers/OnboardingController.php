<?php

namespace Ds\Http\Controllers;

use Ds\Common\Chargebee\BillingPlansService;
use Ds\Domain\Commerce\Currency;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\MissionControl\MissionControlService;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth', ['except' => [
            'getNmiSetup',
            'postNmiSetup',
        ]]);
    }

    /**
     * Starts the onboarding process/survey
     * /onboard/start
     *
     * @return \Illuminate\View\View
     */
    public function start(MissionControlService $missioncontrol)
    {
        $currencies = collect(Currency::getCurrencies());

        $pinnedCurrencies = [
            'USD' => $currencies['USD'],
            'CAD' => $currencies['CAD'],
            'GBP' => $currencies['GBP'],
            'EUR' => $currencies['EUR'],
            'AUD' => $currencies['AUD'],
            'NZD' => $currencies['NZD'],
        ];

        $otherCurrencies = $currencies->reject(function ($currency) use ($pinnedCurrencies) {
            return array_key_exists($currency['code'], $pinnedCurrencies);
        });

        return view('onboarding.start', [
            'pinned_currencies' => $pinnedCurrencies,
            'other_currencies' => $otherCurrencies,
            'market_groups' => $missioncontrol->getMarketGroups(),
        ]);
    }

    /**
     * Processes all the data in the onboarding survey.
     * /onboard/start
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function finish(MissionControlService $missioncontrol)
    {
        // set dp username/password
        sys_set([
            'onboarding_flow' => 0,
            'timezone' => request()->input('timezone') ?? 'America/New_York',
            'timezone_confirmed' => 1,
            'dpo_currency' => request()->input('currency') ?? 'USD',
        ]);

        $preferredCurrency = app(BillingPlansService::class)->currency();

        if ($preferredCurrency === 'CAD' && site()->client->customer_id) {
            app('chargebee')->updateCustomer(site()->client->customer_id, [
                'preferred_currency_code' => app(BillingPlansService::class)->currency(),
            ]);
        }

        if ($preferredCurrency === 'CAD') {
            $missioncontrol->updateSite([
                'txn_fee_currency' => $preferredCurrency,
            ]);
        }

        // update site client
        $missioncontrol->updateClient([
            'number_of_employees' => request('number_of_employees'),
            'annual_fundraising_goal' => request('annual_fundraising_goal'),
            'market_category' => request('market_category'),
        ]);

        // redirect to home page
        return redirect()->to('jpanel?welcome');
    }

    /**
     * @param string $token
     * @return \Illuminate\View\View
     */
    public function getNmiSetup($token)
    {
        $provider = PaymentProvider::provider('nmi')->first();

        if ($provider && $this->validNmiSetupToken($provider, $token)) {
            return view('onboarding.nmi-setup', ['screen' => 'setup']);
        }

        return view('onboarding.nmi-setup', ['screen' => 'invalid']);
    }

    /**
     * @param string $token
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function postNmiSetup($token, Request $request)
    {
        $provider = PaymentProvider::provider('nmi')->first();

        if (! $provider || ! $this->validNmiSetupToken($provider, $token)) {
            return view('onboarding.nmi-setup', ['screen' => 'invalid']);
        }

        $data = $this->validate($request, [
            'credential3' => 'required',
            'is_ach_allowed' => 'boolean',
        ], [
            'credential3.required' => 'API Key is required',
        ]);

        $provider->enabled = true;
        $provider->credential3 = $data['credential3'];
        $provider->is_ach_allowed = $data['is_ach_allowed'] ?? false;
        $provider->config = null;
        $provider->save();

        sys_set([
            'credit_card_provider' => 'nmi',
            'bank_account_provider' => $provider->is_ach_allowed ? 'nmi' : null,
            'kiosk_provider' => 'nmi',
        ]);

        return view('onboarding.nmi-setup', ['screen' => 'done']);
    }

    /**
     * @param \Ds\Domain\Commerce\Models\PaymentProvider $provider
     * @param string $token
     * @return bool
     */
    private function validNmiSetupToken(PaymentProvider $provider, $token): bool
    {
        if (empty($provider->config['setup_link'])) {
            return false;
        }

        [$originalToken, $expires] = explode(':', $provider->config['setup_link']);

        if ($token !== $originalToken || (int) $expires < now()->format('U')) {
            return false;
        }

        return true;
    }
}
