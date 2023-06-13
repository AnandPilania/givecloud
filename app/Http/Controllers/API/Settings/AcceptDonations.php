<?php

namespace Ds\Http\Controllers\API\Settings;

use Ds\Domain\Commerce\Currency;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Http\Controllers\API\Controller;
use Ds\Http\Resources\Settings\AcceptDonationsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AcceptDonations extends Controller
{
    /** @var array */
    protected $supportedCurrencies = ['AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'ISK', 'JMD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KRW', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'STD', 'SZL', 'THB', 'TJS', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW'];

    public function show(): AcceptDonationsResource
    {
        return AcceptDonationsResource::make();
    }

    public function store(Request $request): AcceptDonationsResource
    {
        $provider = PaymentProvider::provider('stripe')->firstOrFail();

        $provider->is_ach_allowed = $request->input('is_ach_allowed') ?? $provider->is_ach_allowed;
        $provider->is_wallet_pay_allowed = $request->input('is_wallet_pay_allowed') ?? $provider->is_wallet_pay_allowed;

        $provider->save();

        if ($provider->is_wallet_pay_allowed && $provider->supports('apple_pay_merchant_domains')) {
            foreach (site()->secure_domains as $domain) {
                $provider->registerApplePayMerchantDomain($domain);
            }
        }

        $localCurrencies = $request->input('is_multicurrency_supported') ?? Currency::hasMultipleCurrencies()
            ? Arr::except($this->supportedCurrencies, Currency::getDefaultCurrencyCode())
            : null;

        sys_set('local_currencies', $localCurrencies);

        return AcceptDonationsResource::make();
    }

    public function disconnect(): AcceptDonationsResource
    {
        $provider = PaymentProvider::query()->provider('stripe')->first();

        $provider->credential1 = null;
        $provider->credential2 = null;
        $provider->credential3 = null;
        $provider->credential4 = null;
        $provider->config = null;
        $provider->enabled = false;

        $provider->save();

        return AcceptDonationsResource::make();
    }
}
