<?php

namespace Ds\Http\Controllers\API;

use Ds\Domain\Commerce\Contracts\Viewable;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Repositories\AccountTypeRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth', ['except' => [
            'login',
        ]]);
    }

    /**
     * Authenticates user credentials and returns auth response the user.
     *
     * @return \Illuminate\Http\Response
     */
    public function login()
    {
        $credentials = [
            'email' => request('email'),
            'password' => request('password'),
        ];

        if (Auth::guard('web')->attempt($credentials)) {
            $user = Auth::guard('web')->user();

            if (empty($user->api_token)) {
                $user->api_token = Str::random(60);
                $user->save();
            }

            return $this->getAuthResponse($user);
        }

        return response(['error' => 'Invalid credentials.'], 401);
    }

    /**
     * Returns auth resonse for the authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function touch()
    {
        $user = Auth::guard('web')->user();

        return $this->getAuthResponse($user);
    }

    /**
     * Retrieve the auth response.
     *
     * @param \Ds\Models\User $user
     * @return \Illuminate\Http\Response
     */
    private function getAuthResponse($user)
    {
        $site = site();

        try {
            $gateways = PaymentProvider::getKioskProvider();
            $gateways = [
                'credit_card' => $gateways,
                'bank_account' => $gateways->is_ach_allowed ? $gateways : null,
            ];
        } catch (ModelNotFoundException $e) {
            $gateways = [];
        }

        $gatewaysHtml = '';
        foreach ($gateways as $provider) {
            if ($provider && $provider->gateway instanceof Viewable) {
                $provider->configureForKioskApp();
                $gatewaysHtml .= $provider->gateway->getView() . PHP_EOL;
            }
        }

        $cards = [];
        foreach (sys_get('list:cardtypes') as $card) {
            switch ($card) {
                case 'v': $cards[] = 'visa'; break;
                case 'm': $cards[] = 'master-card'; break;
                case 'a': $cards[] = 'american-express'; break;
                case 'd': $cards[] = 'discover'; break;
            }
        }

        $weekdays = [];
        foreach (sys_get('list:payment_day_of_week_options') as $day) {
            $weekdays[$day] = day_of_week($day);
        }

        $domain = $site->secure_domain;

        return response([
            'origin' => "https://$domain",
            'account' => sys_get('ds_account_name'),
            'email' => $user->email,
            'api_token' => $user->api_token,
            'features' => [
                'accounts' => feature('accounts'),
                'add_from_vault' => feature('add_from_vault'),
                'buckets' => feature('buckets'),
                'check_ins' => feature('check_ins'),
                'edit_order_items' => feature('edit_order_items'),
                'edownloads' => feature('edownloads'),
                'kiosks' => feature('kiosks'),
                'linked_products' => feature('linked_products'),
                'membership' => feature('membership'),
                'onepage' => feature('onepage'),
                'promos' => feature('promos'),
                'shipping' => feature('shipping'),
                'sites' => feature('sites'),
                'social' => feature('social'),
                'sponsorship' => feature('sponsorship'),
                'stock' => feature('stock'),
                'tax_receipt' => feature('tax_receipt'),
                'taxes' => feature('taxes'),
                'trackorder' => feature('trackorder'),
            ],
            'site_config' => [
                'host' => $domain,
                'version' => 'feature-affinity',
                'csrf_token' => csrf_token(),
                'currency' => [
                    'code' => currency()->code,
                    'symbol' => currency()->symbol,
                ],
                'locale' => [
                    'iso' => $locale = app()->getLocale(),
                    'language' => locale_get_primary_language($locale),
                    'region' => locale_get_region($locale),
                ],
                'provider' => data_get($gateways, 'credit_card.provider', 'givecloudtest'),
                'gateways' => [
                    'credit_card' => data_get($gateways, 'credit_card.provider', false),
                    'bank_account' => data_get($gateways, 'bank_account.provider', false),
                    'paypal' => data_get($gateways, 'paypal.provider', false),
                ],
                'handpoint' => [
                    'enabled' => data_get($gateways, 'credit_card.config.handpoint_enabled', false),
                    'shared_secret' => data_get($gateways, 'credit_card.config.handpoint_shared_secret', ''),
                ],
                'supported_cardtypes' => $cards,
                'processing_fees' => [
                    'amount' => (float) sys_get('dcc_cost_per_order'),
                    'rate' => (float) sys_get('dcc_percentage'),
                ],
                'account_types' => app(AccountTypeRepository::class)->getOnWebAccountTypeDrops(),
                'recaptcha_site_key' => config('services.recaptcha.site_key'),
                'title_options' => sys_get('list:donor_title_options'),
                'referral_sources' => [
                    'enabled' => (bool) sys_get('referral_sources_isactive'),
                    'sources' => sys_get('list:referral_sources_options'),
                    'allow_other' => (bool) sys_get('referral_sources_other'),
                ],
                'billing_country_code' => sys_get('default_country'),
                'shipping_country_code' => sys_get('default_country'),
                'payment_day_options' => sys_get('list:payment_day_options'),
                'payment_weekday_options' => $weekdays,
            ],
            'kiosks' => \Ds\Domain\Kiosk\Models\Kiosk::with('product.customFields')->enabled()->get(),
            'gateways_html' => $gatewaysHtml,
        ]);
    }
}
