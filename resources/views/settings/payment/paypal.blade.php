
@extends('layouts.app')
@section('title', 'Payment Gateways')

@section('content')
<div id="settings-payment-app" @if ($provider->exists and $provider->credential1) v-cloak @endif>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            {{ $name }}

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
                @if ($provider->exists and $provider->credential1)
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-exchange"></i> Connect PayPal</div>
                        <div class="panel-sub-desc">
                            <p>
                                By granting us third party access permissions, we're able to perform
                                PayPal API operations on your behalf.
                            </p>
                            <p>
                                <a href="{{ $provider->gateway->requestPermissionsLink(url("jpanel/settings/payment/{$provider->provider}/reconnect")) }}" class="btn btn-sm btn-default"><i class="fa fa-check-square-o"></i> Verify Settings</a>&nbsp;
                                <a class="btn btn-danger btn-sm" href="/jpanel/settings/payment/{{ $provider->provider }}/disconnect"><i class="fa fa-times"></i> Disconnect</a>
                            </p>
                        </div>
                    </div>
                    <div class="form-horizontal col-sm-6 col-md-8">
                        <div class="row form-group" style="margin-top:10px">
                            <label for="name" class="col-md-4 control-label">Merchant ID</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" value="{{ $provider->credential1 }}" readonly>
                                <small class="text-muted">This is the special PayPal identifier we need to process payments for you.</small>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="button" class="btn btn-info btn-sm paypal-test">Test Connection</button>
                                <button type="button" class="btn btn-info btn-sm paypal-reference-test">Test Reference Transactions</button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-exchange"></i> Connect PayPal</div>
                        <div class="panel-sub-desc">Sign up for a PayPal account or link an existing PayPal account.</div>
                    </div>
                    <div class="col-sm-6 col-md-8">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4" style="margin-top:20px">
                                <div dir="ltr" style="text-align:left" trbidi="on">
                                <script>(function(d, s, id){
                                 var js, ref = d.getElementsByTagName(s)[0];
                                 if (!d.getElementById(id)){
                                 js = d.createElement(s); js.id = id; js.async = true;
                                 js.src ="https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js";
                                 ref.parentNode.insertBefore(js, ref);
                                 }
                                 }(document, "script", "paypal-js"));
                                </script>
                                <a class="btn btn-primary btn-lg" data-paypal-button="true" href="{{ $provider->getAuthenticationUrl(url("jpanel/settings/payment/{$provider->provider}/connect")) }}" target="PPFrame">
                                    Connect PayPal
                                </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="panel panel-info">
        <div class="panel-heading visible-xs">
            <i class="fa fa-exclamation-circle"></i> Recurring Donations with PayPal

        </div>
        <div class="panel-body">
            <div class="panel-sub-title">
                <i class="fa fa-exclamation-circle"></i> Recurring Donations with PayPal
                <toggle-button v-model="input.config.reference_transactions" @change="updateProvider"></toggle-button>
            </div>
            <p>'<strong>Reference Transactions</strong>' is a permission PayPal gives you (a merchant) to collect recurring payment from your donor/customer. It is mandatory that you have reference transactions enabled in your PayPal Live Account. To enable this feature, you must contact <a href="https://www.paypal-techsupport.com/app/ask" target="_blank">PayPal's Merchant Technical Support <i class="fa fa-external-link"></i></a> and specify the business account in which you would want this feature to be enabled. Additional requirements enforced by PayPal may need to be met to enable this feature.</p>
        </div>
    </div>
    </div>
</div>

</div>


<style>
    .panel.panel-info { border-color:#bce8f1; }
    .panel.panel-info .panel-body { color:#31708f; background-color:#d9edf7; }
    .panel.panel-info .panel-sub-title { color:#31708f; border-bottom-color:#bce8f1; }
    .panel.panel-info p { font-size:16px; }
</style>

@if ($provider->exists and $provider->credential1)
<script>
spaContentReady(function($) {
    settingsPaymentProvider('#settings-payment-app', {!! json_encode([
        'provider' => $provider->provider,
        'exists'   => $provider->exists,
        'enabled'  => $provider->enabled,
        'input' => [
            'enabled'             => true,
            'provider'            => $provider->provider,
            'credential1'         => $provider->credential1,
            'credential2'         => $provider->credential2,
            'credential3'         => $provider->credential3,
            'credential4'         => $provider->credential4,
            'config'              => array_merge([
                'reference_transactions' => true,
            ], $provider->config ?? []),
            'show_payment_method' => $provider->show_payment_method,
            'require_cvv'         => $provider->require_cvv,
            'is_ach_allowed'      => $provider->is_ach_allowed,
            'duplicate_window'    => $provider->duplicate_window,
            'test_mode'           => $provider->test_mode,
        ],
    ], JSON_PRETTY_PRINT) !!});
});
</script>
@endif
@endsection
