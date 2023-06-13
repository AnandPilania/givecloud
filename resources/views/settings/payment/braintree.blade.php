
@extends('layouts.app')
@section('title', 'Payment Gateways')

@section('content')
<div id="settings-payment-app" v-cloak>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Braintree
            <div class="pull-right">
                <div v-if="exists" class="pull-right ml-2">
                    <toggle-button @change="updateEnabled" v-model="enabled" :width="120" :height="34" :labels="{checked: 'ENABLED', unchecked: 'DISABLED'}"></toggle-button>
                </div>
            </div>
        </h1>
    </div>
</div>

<div id="settings-payment-gateway" class="row">
    <div class="col-md-12 col-lg-10 col-lg-offset-1">
        {{ app('flash')->output() }}

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="herospace col-md-12">
                    To integrate the Braintree payment gateway into your site, you require an active merchant account and Braintree gateway api public key, api private key and merchant ID.
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="col-md-12 col-lg-10 col-lg-offset-1">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-5">

                    <div class="form-group">
                        <label>
                            Merchant ID
                        </label>
                        <input type="text" class="form-control" v-model="input.config.merchant_id" name="merchant_id" required>
                    </div>

                    @foreach ($currencies as $currency)
                    <div class="form-group">
                        <label>
                            Merchant Account ID ({{ $currency->code }})
                        </label>
                        <input type="text" class="form-control" v-model="input.config.merchant_account_id.{{ $currency->code }}" name="merchant_account_id_{{ $currency->code }}">
                    </div>
                    @endforeach

                    <div class="form-group">
                        <label>
                            API Public Key
                        </label>
                        <input type="text" class="form-control" v-model="input.config.api_public_key" name="api_public_key" required>
                    </div>

                    <div class="form-group has-feedback">
                        <label>
                            API Private Key
                        </label>
                        <input type="password" class="form-control password" v-model="input.config.api_private_key" name="api_private_key" required>
                        <i class="glyphicon glyphicon-eye-open form-control-feedback"></i>
                    </div>

                    <div class="form-group">
                        <label>
                            Test Mode
                        </label>
                        <select class="form-control" v-model="input.test_mode" name="test_mode">
                            <option :value="false">No (Recommended)</option>
                            <option :value="true">Yes</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>
                            Allow ACH
                        </label>&nbsp;&nbsp;&nbsp;
                        <toggle-button @disabled(! array_key_exists('USD', $currencies)) v-model="input.is_ach_allowed"></toggle-button>
                        <div class="text-xs">
                            ACH is only available for merchants who are located in the United States and make transactions in USD.
                        </div>
                    </div>

                    @if (feature('fundraising_forms'))
                    <div class="form-group">
                        <label>
                            Enable Apple Pay
                        </label>&nbsp;&nbsp;&nbsp;
                        <toggle-button v-model="input.config.is_apple_pay_allowed"></toggle-button>
                    </div>

                    <div class="form-group">
                        <label>
                            Enable Google Pay
                        </label>&nbsp;&nbsp;&nbsp;
                        <toggle-button v-model="input.config.is_google_pay_allowed" :sync="true"></toggle-button>
                    </div>

                    <div class="form-group -mt-2 ml-4" v-show="input.config.is_google_pay_allowed">
                        <label class="text-xs">
                            Google Merchant ID
                        </label>
                        <input type="text" class="form-control" v-model="input.config.google_merchant_id" name="google_merchant_id">
                        <div class="text-xs mt-1">
                            Once registered, click the Google Pay API tab in the Google Pay and Wallet Console to get your Google Merchant ID.
                            <a href="https://pay.google.com/business/console"  rel="noreferrer" target="_blank">https://pay.google.com/business/console</a>
                        </div>
                    </div>
                    @endif

                    <div class="form-group">
                        <vue-ladda class="btn btn-success" @click="updateProvider" :loading="saving" data-style="expand-left" data-color="green">
                            <strong>Update information</strong>
                        </vue-ladda>
                    </div>

                </div>

                <div class="col-md-5 col-md-offset-1">
                </div>

            </div>
        </div>

    </div>
    </div>
</div>

</div>

<script>
spaContentReady(function($) {
    function updateProviderInputFilter(input) {
        if (input.config.is_google_pay_allowed && !input.config.google_merchant_id && !input.test_mode) {
            input.config.is_google_pay_allowed = false
        }

        input.is_wallet_pay_allowed = Boolean(input.config.is_apple_pay_allowed || input.config.is_google_pay_allowed)

        return input;
    }

    settingsPaymentProvider('#settings-payment-app', {!! json_encode([
        'provider' => $provider->provider,
        'exists'   => $provider->exists,
        'enabled'  => $provider->enabled,
        'saving'   => false,
        'input' => [
            'enabled'             => true,
            'provider'            => $provider->provider,
            'credential1'         => $provider->credential1,
            'credential2'         => $provider->credential2,
            'credential3'         => $provider->credential3,
            'credential4'         => $provider->credential4,
            'config'              => array_merge([
                'merchant_id'                => '',
                'merchant_account_id'        => [$currency_code => ''],
                'api_public_key'            => '',
                'api_private_key'            => '',
                'is_apple_pay_allowed'       => false,
                'is_google_pay_allowed'      => false,
                'google_merchant_id'         => '',
            ], $provider->config ?? []),
            'show_payment_method' => $provider->show_payment_method,
            'require_cvv'         => $provider->require_cvv,
            'is_ach_allowed'      => $provider->is_ach_allowed && array_key_exists('USD', $currencies),
            'is_wallet_pay_allowed' => $provider->is_wallet_pay_allowed,
            'duplicate_window'    => 1200,
            'test_mode'           => $provider->test_mode,
        ],
    ], JSON_PRETTY_PRINT) !!}, updateProviderInputFilter);
});
</script>
@endsection
