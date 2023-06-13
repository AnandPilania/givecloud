
@extends('layouts.app')
@section('title', 'Payment Gateways')

@section('content')
<div id="settings-payment-app" v-cloak>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Stripe

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

    @if ($provider->exists || ! sys_get('use_stripe_connect'))
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-gear"></i> Stripe Settings</div>
                        <div class="panel-sub-desc">
                            @if (sys_get('use_stripe_connect'))
                                <p></p>
                            @else
                                <i class="fa fa-question-circle"></i> <strong>Where do I find the Publishable/Secret Key(s)?</strong><br>
                                <ol class="mt-1">
                                    <li>Go to your <a class="text-muted" href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard</a>.</li>
                                    <li>From the left menu, select Developer > API Keys.</li>
                                </ol>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-8 pt-3.5">
                        @unless (sys_get('use_stripe_connect'))
                            <div class="form-group">
                                <label for="inputPublishableKey">Publishable Key</label>
                                <input type="text" id="inputPublishableKey" class="form-control" v-model="input.config.publishable_key" name="publishable_key" required>
                            </div>
                            <div class="form-group has-feedback">
                                <label for="inputSecretKey">Secret Key</label>
                                <input type="password" id="inputSecretKey" class="form-control password" v-model="input.config.secret_key" name="secret_key" required>
                                <i class="glyphicon glyphicon-eye-open form-control-feedback"></i>
                            </div>
                        @endunless

                        @if (feature('fundraising_forms'))
                        <div class="form-group">
                            <label>
                                Enable Apple/Google Pay
                            </label>&nbsp;&nbsp;&nbsp;
                            <toggle-button v-model="input.is_wallet_pay_allowed"></toggle-button>
                        </div>
                        @endif

                        <div class="form-group mb-2">
                            <vue-ladda class="btn btn-success" @click="updateProvider" :loading="saving" data-style="expand-left" data-color="green">
                                <strong>Update information</strong>
                            </vue-ladda>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($provider->exists)
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-exchange"></i> Stripe Webhook</div>
                    <div class="panel-sub-desc">
                        <p>
                            Givecloud uses the webhook feature of Stripe to listen to events occurring in your Stripe account
                        </p>
                    </div>
                </div>
                <div class="col-sm-6 col-md-8">
                    <h3 style="margin-top:0">How to configure?</h3>
                    <p>In your Stripe account</p>
                    <ol style="padding-left:30px;list-style-type:decimal">
                        <li>Disable <strong>View test data</strong>.</li>
                        <li>Go to <strong>Developers &gt; Webhooks &gt; Add endpoint</strong>.</li>
                        <li>Paste the Notification URL given below in the field <strong>URL to be called</strong>.</li>
                    </ol>
                    <dl style="margin-bottom:12px;">
                        <dt><strong>Notification URL</strong></dt>
                        <dd>
                            <input class="form-control" value="<?= e(secure_site_url('/webhook/stripe', true)) ?>" onclick="this.select();" readonly>
                        </dd>
                    </dl>
                    <ol start="4" style="padding-left:30px;list-style-type:decimal">
                        <li>Select the endpoint you just created and then select the <strong>Click to reveal</strong> button to obtain the secret.</li>
                        <li>Paste the Secret given in the field below and click Update information.</li>
                    </ol>
                    <div class="form-group" style="margin-top:20px">
                        <label>
                            Signing secret
                            <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="The endpoint's secret retrieved from your Stripe Dashboard."></i>
                        </label>
                        <input type="password" class="form-control" v-model="input.config.signing_secret" name="signing_secret" required>
                    </div>
                    <div class="form-group mb-1">
                        <vue-ladda class="btn btn-success" @click="updateProvider" :loading="saving" data-style="expand-left" data-color="green">
                            <strong>Update information</strong>
                        </vue-ladda>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if (sys_get('use_stripe_connect'))
    <div class="panel panel-default">
        @if ($provider->exists)
            <div class="panel-body alert-warning">
                <div class="row">
                    <div class="herospace col-md-12">
                        <h3 style="margin-top:0">
                            <i class="fa fa-warning fa-fw"></i>
                            Connection problems?
                        </h3>
                        <p>
                            If you're having connection problems with Stripe you may need to reconnect your account.<br>
                            Simply click on the button below to reconnect your Stripe account with Givecloud.
                        </p>
                        <a class="mt-3 inline-block connect" href="{{ $provider->getAuthenticationUrl() }}">
                            <img src="/jpanel/assets/images/payment/connect-with-stripe.png" alt="Connect with Stripe">
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="panel-body">
                <div class="row">
                    <div class="herospace col-md-12">
                        <p>
                            Getting started is easy, simply click on the button below to connect your Stripe account with Givecloud.
                            If you don't have a Stripe account, you'll be prompted to create a free account.
                        </p>
                        <a class="mt-3 inline-block connect" href="{{ $provider->getAuthenticationUrl() }}">
                            <img src="/jpanel/assets/images/payment/connect-with-stripe.png" alt="Connect with Stripe">
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @endif

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
            'config'              => $provider->config ?? [
                'signing_secret' => '',
                'default_currency' => '',
            ],
            'show_payment_method' => $provider->show_payment_method,
            'require_cvv'         => $provider->require_cvv,
            'is_ach_allowed'      => $provider->is_ach_allowed,
            'is_wallet_pay_allowed' => $provider->is_wallet_pay_allowed,
            'duplicate_window'    => $provider->duplicate_window,
            'test_mode'           => $provider->test_mode,
        ],
    ], JSON_PRETTY_PRINT) !!});
});
</script>
@endsection
