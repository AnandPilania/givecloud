<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Commerce\Currency;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Commerce\PaymentProviderService;
use Ds\Domain\MissionControl\MissionControlService;
use Ds\Domain\QuickStart\Events\QuickStartTaskAffected;
use Ds\Domain\QuickStart\Tasks\SetupLiveGateway;
use Ds\Domain\QuickStart\Tasks\TurnOnLiveGateway;
use Ds\Domain\Shared\Exceptions\DisclosableException;
use Ds\Domain\Shared\Exceptions\MessageException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class PaymentProviderController extends Controller
{
    protected function registerMiddleware(): void
    {
        parent::registerMiddleware();

        $this->middleware('requires.permissions:admin.payments');
    }

    public function showIndex()
    {
        $gateways = collect([
            'authorizenet',
            'braintree',
            'caymangateway',
            'givecloudtest',
            'gocardless',
            'nmi',
            // 'paypalcheckout',
            'paypalexpress',
            'paysafe',
            'safesave',
            'stripe',
            'vanco',
        ]);

        if (isGivecloudExpress()) {
            $gateways = collect([
                'givecloudtest',
                'paypalexpress',
                'stripe',
            ]);
        }

        $gateways = $gateways->map(function ($name) {
            $provider = $this->getPaymentProvider($name);
            $data = (object) [
                'id' => $name,
                'name' => $provider->gateway->getDisplayName(),
                'setup' => $provider->exists,
                'enabled' => $provider->enabled,
                'link' => $provider->supports('oauth') ? $provider->getAuthenticationUrl() : null,
                'provider' => $provider->provider,
            ];
            if ($name === 'paypalcheckout' || $name === 'paypalexpress') {
                $data->setup = $provider->exists && $provider->credential1;
            }

            return $data;
        })->keyBy('id');

        return view('settings.payment.index', [
            '__menu' => 'admin.advanced',
            'gateways' => $gateways,
            'creditCardProvider' => data_get(PaymentProvider::getCreditCardProvider(false), 'provider', ''),
            'bankAccountProvider' => data_get(PaymentProvider::getBankAccountProvider(false), 'provider', ''),
            'kioskProvider' => data_get(PaymentProvider::getKioskProvider(false), 'provider', ''),
        ]);
    }

    /**
     * Show the Authorize.Net settings.
     *
     * @return \Illuminate\View\View
     */
    public function showAuthorizeNet()
    {
        $provider = $this->getPaymentProvider('authorizenet');

        return view('settings.payment.authorizenet', [
            '__menu' => 'admin.advanced',
            'provider' => $provider,
        ]);
    }

    public function showBraintree(): View
    {
        $provider = $this->getPaymentProvider('braintree');

        return view('settings.payment.braintree', [
            '__menu' => 'admin.advanced',
            'provider' => $provider,
            'currency_code' => sys_get('dpo_currency'),
            'currencies' => Currency::getLocalCurrencies(),
        ]);
    }

    /**
     * Show the Cayman Gateway settings.
     *
     * @return \Illuminate\View\View
     */
    public function showCaymanGateway()
    {
        $provider = $this->getPaymentProvider('caymangateway');

        return view('settings.payment.caymangateway', [
            '__menu' => 'admin.advanced',
            'provider' => $provider,
        ]);
    }

    /**
     * Show the NMI settings.
     *
     * @return \Illuminate\View\View
     */
    public function showGivecloudTest()
    {
        $provider = $this->getPaymentProvider('givecloudtest');

        return view('settings.payment.givecloudtest', [
            '__menu' => 'admin.advanced',
            'provider' => $provider,
        ]);
    }

    /**
     * Show the GoCardless Gateway settings.
     *
     * @return \Illuminate\View\View
     */
    public function showGoCardless()
    {
        $provider = $this->getPaymentProvider('gocardless');

        /** @var \Ds\Domain\Commerce\Gateways\GoCardlessGateway */
        $gateway = $provider->gateway;

        return view('settings.payment.gocardless', [
            '__menu' => 'admin.advanced',
            'provider' => $provider,
            'connect_link' => $provider->getAuthenticationUrl(),
            'onboarding_link' => $gateway->getOnboardingUrl(),
            'verification_status' => $gateway->getVerificationStatus(),
        ]);
    }

    /**
     * Show the NMI settings.
     *
     * @return \Illuminate\View\View
     */
    public function showNMI()
    {
        $provider = $this->getPaymentProvider('nmi');

        return view('settings.payment.nmi', [
            '__menu' => 'admin.advanced',
            'name' => 'NMI',
            'provider' => $provider,
        ]);
    }

    /**
     * Show the PayPal settings.
     *
     * @return \Illuminate\View\View
     */
    public function showPayPal($type)
    {
        $provider = $this->getPaymentProvider("paypal{$type}");

        return view('settings.payment.paypal', [
            '__menu' => 'admin.advanced',
            'name' => $provider->gateway->getDisplayName(),
            'provider' => $provider,
        ]);
    }

    /**
     * Show the Paysafe settings.
     *
     * @return \Illuminate\View\View
     */
    public function showPaysafe()
    {
        $provider = $this->getPaymentProvider('paysafe');

        return view('settings.payment.paysafe', [
            '__menu' => 'admin.advanced',
            'provider' => $provider,
            'currency_code' => sys_get('dpo_currency'),
            'currencies' => Currency::getLocalCurrencies(),
        ]);
    }

    /**
     * Show the Safe Save settings.
     *
     * @return \Illuminate\View\View
     */
    public function showSafeSave()
    {
        $provider = $this->getPaymentProvider('safesave');

        return view('settings.payment.nmi', [
            '__menu' => 'admin.advanced',
            'name' => 'Safe Save Payments',
            'provider' => $provider,
        ]);
    }

    /**
     * Show the Stripe settings.
     *
     * @return \Illuminate\View\View
     */
    public function showStripe()
    {
        $provider = $this->getPaymentProvider('stripe');

        return view('settings.payment.stripe', [
            '__menu' => 'admin.advanced',
            'provider' => $provider,
        ]);
    }

    /**
     * Show the Vanco settings.
     *
     * @return \Illuminate\View\View
     */
    public function showVanco()
    {
        $provider = $this->getPaymentProvider('vanco');

        return view('settings.payment.vanco', [
            '__menu' => 'admin.advanced',
            'provider' => $provider,
        ]);
    }

    /**
     * Connect a GoCardless account.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function connectGoCardless()
    {
        try {
            $provider = $this->getPaymentProvider('gocardless');

            /** @var \Ds\Domain\Commerce\Gateways\GoCardlessGateway */
            $gateway = $provider->gateway;

            $res = $gateway->getAccessToken();
            $provider->enabled = true;
            $provider->is_ach_allowed = true;
            $provider->credential1 = $res->getAccountId();
            $provider->credential2 = $res->getAccessToken();
            $provider->save();

            return redirect()->to('jpanel/settings/payment/gocardless');
        } catch (DisclosableException $e) {
            return redirect()->to('jpanel/settings/payment/gocardless')->with('error', $e->getMessage());
        }

        return redirect()->to('jpanel/settings/payment/gocardless')
            ->with('error', 'There was a problem connecting your GoCardless account.');
    }

    /**
     * Disconnect a GoCardless account.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disconnectGoCardless()
    {
        $provider = $this->getPaymentProvider('gocardless');
        $provider->credential1 = null;
        $provider->credential2 = null;
        $provider->credential3 = null;
        $provider->credential4 = null;
        $provider->config = null;
        $provider->save();

        return redirect()->to('jpanel/settings/payment/gocardless');
    }

    /**
     * Verify a GoCardless connection.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyGoCardless()
    {
        $provider = $this->getPaymentProvider('gocardless');

        /** @var \Ds\Domain\Commerce\Gateways\GoCardlessGateway */
        $gateway = $provider->gateway;

        return response()->json($gateway->verifyAccessToken());
    }

    /**
     * Connect a PayPal account.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function connectPayPal($type)
    {
        try {
            if (request('productIntentId') !== 'addipmt') {
                throw new MessageException('Unsupported PayPal product.');
            }

            if (request('consentStatus') !== 'true') {
                throw new MessageException('Consent to share credentials was denied.');
            }

            if (request('permissionsGranted') !== 'true') {
                throw new MessageException('Requested permissions were not granted.');
            }

            if (request('merchantIdInPayPal')) {
                $provider = $this->getPaymentProvider("paypal{$type}", 'paypal');

                $provider->enabled = true;
                $provider->credential1 = request('merchantIdInPayPal');
                $provider->credential2 = request('accountStatus');
                $provider->save();

                return redirect()->to("jpanel/settings/payment/paypal{$type}")
                    ->with('success', request('returnMessage'));
            }
        } catch (DisclosableException $e) {
            return redirect()->to("jpanel/settings/payment/paypal{$type}")->with('error', $e->getMessage());
        }

        return redirect()->to("jpanel/settings/payment/paypal{$type}")
            ->with('error', 'There was a problem connecting your PayPal account.');
    }

    /**
     * Reconnect a PayPal account.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reconnectPayPal($type)
    {
        try {
            if (! request('request_token')) {
                throw new MessageException('There was a problem verifying third party permissions with PayPal.');
            }

            $provider = $this->getPaymentProvider("paypal{$type}", 'paypal');

            /** @var \Ds\Domain\Commerce\Gateways\PayPalExpressGateway */
            $gateway = $provider->gateway;

            $res = $gateway->getAccessToken();
            $provider->credential3 = $res->getAccessToken();
            $provider->credential4 = $res->getTokenSecret();
            $provider->save();
        } catch (DisclosableException $e) {
            return redirect()->to("jpanel/settings/payment/paypal{$type}")->with('error', $e->getMessage());
        }

        return redirect()->to("jpanel/settings/payment/paypal{$type}")
            ->with('success', 'PayPal third party permissions confirmed.');
    }

    /**
     * Disconnect a PayPal account.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disconnectPayPal($type)
    {
        $provider = $this->getPaymentProvider("paypal{$type}", 'paypal');
        $provider->credential1 = null;
        $provider->credential2 = null;
        $provider->credential3 = null;
        $provider->credential4 = null;
        $provider->save();

        return redirect()->to("jpanel/settings/payment/paypal{$type}");
    }

    /**
     * Connect a Stripe account.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function connectStripe()
    {
        $redirectTo = site()->client->tier === 'GCX'
                ? 'jpanel/settings/general'
                : 'jpanel/settings/payment/stripe';

        try {
            $provider = $this->getPaymentProvider('stripe');

            /** @var \Ds\Domain\Commerce\Gateways\StripeGateway */
            $gateway = $provider->gateway;

            $res = $gateway->getAccessToken();
            $provider->enabled = true;
            $provider->credential1 = $res->getAccountId();
            $provider->credential2 = $res->getAccessToken();
            $provider->credential3 = $res->getRefreshToken();
            $provider->credential4 = $res->getPublishableKey();
            $provider->save();

            if (isGivecloudExpress()) {
                sys_set('credit_card_provider', 'stripe');
                sys_set('bank_account_provider', 'stripe');
            }

            Http::pool(fn (Pool $pool) => [
                $pool->withToken(config('services.missioncontrol.api_token'))
                    ->post(MissionControlService::getMissionControlApiUrl('hubspot/sync/' . site()->client_id)),
                $pool->withToken(config('services.missioncontrol.api_token'))
                    ->post(MissionControlService::getMissionControlApiUrl('intercom/sync/' . site()->client_id)),
            ]);

            return redirect()->to($redirectTo);
        } catch (DisclosableException $e) {
            return redirect()->to($redirectTo)->with('error', $e->getMessage());
        }

        return redirect()->to($redirectTo)
            ->with('error', 'There was a problem connecting your Stripe account.');
    }

    /**
     * Disconnect a Stripe account.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disconnectStripe()
    {
        $provider = $this->getPaymentProvider('stripe');
        $provider->credential1 = null;
        $provider->credential2 = null;
        $provider->credential3 = null;
        $provider->credential4 = null;
        $provider->config = null;
        $provider->save();

        return redirect()->to('jpanel/settings/payment/stripe');
    }

    /**
     * Store provider settings.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeProvider(Request $request)
    {
        $provider = $this->getPaymentProvider($request->input('provider'));

        collect([
            'enabled',
            'credential1',
            'credential2',
            'credential3',
            'credential4',
            'config',
            'show_payment_method',
            'require_cvv',
            'is_ach_allowed',
            'is_wallet_pay_allowed',
            'duplicate_window',
            'test_mode',
        ])->each(function ($attribute) use ($request, $provider) {
            if ($request->filled($attribute)) {
                $provider->setAttribute($attribute, $request->input($attribute));
            }
        });

        $provider->save();

        if ($provider->is_wallet_pay_allowed && $provider->supports('apple_pay_merchant_domains')) {
            foreach (site()->secure_domains as $domain) {
                $provider->registerApplePayMerchantDomain($domain);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete a provider.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteProvider()
    {
        $input = request('gateway');

        $provider = PaymentProvider::where('provider', Arr::get($input, 'provider'))
            ->firstOrFail();

        $provider->delete();

        return redirect()->to('jpanel/settings/payment');
    }

    /**
     * Set the default providers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function setDefaultProviders()
    {
        sys_set([
            'credit_card_provider' => (string) request('credit_card_provider'),
            'bank_account_provider' => (string) request('bank_account_provider'),
            'kiosk_provider' => (string) request('kiosk_provider'),
        ]);

        QuickStartTaskAffected::dispatch(SetupLiveGateway::initialize());
        QuickStartTaskAffected::dispatch(TurnOnLiveGateway::initialize());

        return response()->json(['success' => true]);
    }

    /**
     * Get a payment provider.
     *
     * @param string $name
     * @param string $type
     * @return \Ds\Domain\Commerce\Models\PaymentProvider
     */
    private function getPaymentProvider(string $name, string $type = 'credit'): PaymentProvider
    {
        return app(PaymentProviderService::class)->firstOrCreate($name, $type);
    }
}
