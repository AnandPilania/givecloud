
@extends('layouts.app')
@section('title', 'Payment Gateways')

@section('content')
<div id="settings-payment-app" v-cloak>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Paysafe

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
                    To integrate the Paysafe payment gateway into your site, you require an active merchant account and Paysafe gateway api key username, api key password and account number.
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
                            API Key Username
                        </label>
                        <input type="text" class="form-control" v-model="input.config.api_key_user" name="api_key_user" required>
                    </div>

                    <div class="form-group has-feedback">
                        <label>
                            API Key Password
                        </label>
                        <input type="password" class="form-control password" v-model="input.config.api_key_pass" name="api_key_pass" required>
                        <i class="glyphicon glyphicon-eye-open form-control-feedback"></i>
                    </div>

                    <div class="form-group">
                        <label>
                            Single-Use Token Username
                        </label>
                        <input type="text" class="form-control" v-model="input.config.token_user" name="token_user" required>
                    </div>

                    <div class="form-group has-feedback">
                        <label>
                            Single-Use Token Password
                        </label>
                        <input type="password" class="form-control password" v-model="input.config.token_pass" name="token_pass" required>
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

                    @foreach ($currencies as $currency)
                        <div class="form-group">
                            <label>
                                Account Number ({{ $currency->code }})
                            </label>
                            <input type="text" class="form-control" v-model="input.config.accounts.{{ $currency->code }}" name="accounts_{{ $currency->code }}" required>
                        </div>

                        @if ($currency->code === 'USD')
                            <div class="form-group">
                                <label>
                                    Account Number ({{ $currency->code }} - ACH)
                                </label>
                                <input type="text" class="form-control" v-model="input.config.ach_account_number" name="ach_account_number">
                            </div>
                        @endif

                        @if ($currency->code === 'CAD')
                            <div class="form-group">
                                <label>
                                    Account Number ({{ $currency->code }} - EFT)
                                </label>
                                <input type="text" class="form-control" v-model="input.config.eft_account_number" name="eft_account_number">
                            </div>

                            {{--
                            <div class="form-group">
                                <label>
                                    Account Number ({{ $currency->code }} - INTERAC)
                                </label>
                                <input type="text" class="form-control" v-model="input.config.interac_account_number" name="interac_account_number">
                            </div>
                            --}}
                        @endif
                    @endforeach

                    <div class="form-group">
                        <label>
                            Use "Paysafe Checkout"
                        </label>&nbsp;&nbsp;&nbsp;
                        <toggle-button v-model="input.config.use_checkout"></toggle-button>
                    </div>

                    <div class="form-group" v-show="input.config.use_checkout">
                        <label>
                            Preferred Payment Method
                            <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="The payment method selected when Paysafe Checkout is opened."></i>
                        </label>
                        <select class="form-control" v-model="input.config.checkout_preferred" name="checkout_preferred">
                            <option>Cards</option>
                            <option>DirectDebit</option>
                            <option>Interac</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>
                            Enable 3D Secure 2
                        </label>&nbsp;&nbsp;&nbsp;
                        <toggle-button v-model="input.config.use_3ds2"></toggle-button>
                    </div>

                    <div class="form-group">
                        <label>
                            Enable Handpoint Card Readers
                        </label>&nbsp;&nbsp;&nbsp;
                        <toggle-button v-model="input.config.handpoint_enabled"></toggle-button>
                    </div>

                    <div class="form-group has-feedback" v-show="input.config.handpoint_enabled">
                        <label>
                            Handpoint Device Shared Secret
                            <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Used to link a merchant with card readers."></i>
                        </label>
                        <input type="password" class="form-control password" v-model="input.config.handpoint_shared_secret" name="handpoint_secret" required>
                        <i class="glyphicon glyphicon-eye-open form-control-feedback"></i>
                    </div>

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
                'accounts'                => [$currency_code => ''],
                'api_key_user'            => '',
                'api_key_pass'            => '',
                'token_user'              => '',
                'token_pass'              => '',
                'use_3ds2'                => false,
                'use_checkout'            => false,
                'checkout_preferred'      => 'Cards',
                'handpoint_enabled'       => false,
                'handpoint_shared_secret' => '',
            ], $provider->config ?? []),
            'show_payment_method' => $provider->show_payment_method,
            'require_cvv'         => $provider->require_cvv,
            'is_ach_allowed'      => $provider->is_ach_allowed,
            'duplicate_window'    => 1200,
            'test_mode'           => $provider->test_mode,
        ],
    ], JSON_PRETTY_PRINT) !!});
});
</script>
@endsection
