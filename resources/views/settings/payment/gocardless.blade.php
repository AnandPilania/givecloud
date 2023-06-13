
@extends('layouts.app')
@section('title', 'Payment Gateways')

@section('content')
<div id="settings-payment-app" @if ($provider->exists and $provider->credential1) v-cloak @endif>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            GoCardless

            <div class="pull-right">

                <div v-if="exists" class="pull-right ml-2">
                    <toggle-button @change="updateEnabled" v-model="enabled" :width="120" :height="34" :labels="{checked: 'ENABLED', unchecked: 'DISABLED'}">
                </div>
            </div>
        </h1>
    </div>
</div>


<div id="settings-payment-gateway" class="row" :class="{ disabled: exists && !enabled }">
    <div class="col-md-12 col-lg-10 col-lg-offset-1">
        {{ app('flash')->output() }}

    @if ($provider->exists and $provider->credential1)

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group mt-3 clearfix">
                            <label for="name" class="col-md-2 control-label" style="margin-top:6px">Organization ID</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" value="{{ $provider->credential1 }}" maxlength="" readonly="readonly" style="max-width:450px" />
                            </div>
                        </div>
                        <div class="form-group -mt-2">
                            <div class="col-md-10 col-md-offset-2">
                                <a class="btn btn-danger btn-sm" href="/jpanel/settings/gocardless/disconnect">Disconnect</a>
                                <button type="button" class="btn btn-info btn-sm gocardless-test">Test Connection</button>
                            </div>
                        </div>
                        <div class="col-md-10 col-md-offset-2" style="margin-top:10px">
                        @if ($verification_status === 'action_needed')
                            <p class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Further information required to verify your account so you can receive payouts.</p>
                            <a class="btn btn-primary btn-lg" href="{{ $onboarding_link }}">
                                Proceed with onboarding and verification process
                            </a>
                        @elseif ($verification_status === 'in_review')
                            <p class="text-warning"><i class="fa fa-clock-o" aria-hidden="true"></i> Awaiting review by GoCardless.</p>
                        @else
                            <p class="text-success"><i class="fa fa-check" aria-hidden="true"></i> Account is fully verified.</p>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-body alert-warning">
                <div class="row">
                    <div class="herospace col-md-12">
                        <h3 style="margin-top:0">
                            <i class="fa fa-warning fa-fw"></i>
                            Connection problems?
                        </h3>
                        <p class="text-base">
                            If you're having connection problems with GoCardless you may need to reconnect your account.<br>
                            Simply click on the button below to reconnect your GoCardless account with Givecloud.
                        </p>
                        <div class="mt-2">
                            <a class="btn btn-warning" href="{{ $connect_link }}">
                                Connect GoCardless
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="herospace col-md-12">
                        <p>
                            Getting started is easy, simply click on the button below to connect your GoCardless account with Givecloud.
                            If you don't have a GoCardless account, you'll be prompted to create a free account.
                        </p>
                        <p>
                            <a class="btn btn-primary btn-lg" href="{{ $connect_link }}">
                                Connect GoCardless
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
            'credential1'         => $provider->credential1,
            'credential2'         => $provider->credential2,
            'credential3'         => $provider->credential3,
            'credential4'         => $provider->credential4,
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
