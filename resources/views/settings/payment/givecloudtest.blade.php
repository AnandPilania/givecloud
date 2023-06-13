
@extends('layouts.app')
@section('title', 'Payment Gateways')

@section('content')
<div id="settings-payment-app" v-cloak>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Givecloud Test Gateway

            <div class="pull-right">
                <toggle-button @change="updateEnabled" v-model="enabled" :width="120" :height="34" :labels="{checked: 'ENABLED', unchecked: 'DISABLED'}"></toggle-button>
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
                    The Givecloud Test Gateway allows you to simulate an end to end transaction as if you were using a real, live payment gateway.
                    This gateway is for testing purposes only and should not be enabled on a production site.
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-5">

                    <div class="form-group">
                        <label>
                            Allow ACH
                        </label>&nbsp;&nbsp;&nbsp;
                        <toggle-button v-model="input.is_ach_allowed"></toggle-button>
                    </div>

                    @if (feature('fundraising_forms'))
                    <div class="form-group">
                        <label>
                            Enable Apple/Google Pay
                        </label>&nbsp;&nbsp;&nbsp;
                        <toggle-button v-model="input.is_wallet_pay_allowed"></toggle-button>
                    </div>
                    @endif

                    <div class="form-group">
                        <vue-ladda class="btn btn-success" @click="updateProvider" :loading="saving" data-style="expand-left" data-color="green">
                            <strong>Update information</strong>
                        </vue-ladda>
                    </div>

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
