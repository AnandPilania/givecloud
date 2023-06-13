
@extends('layouts.app')
@section('title', 'Payment Gateways')

@section('content')
<div id="settings-payment-app" v-cloak>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Cayman Gateway

            <div v-if="exists" class="pull-right">
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
                    To integrate the Cayman Gateway payment gateway into your site, you require an active merchant account and Cayman Gateway API key.
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

                    <div class="form-group has-feedback">
                        <label>
                            API Key
                        </label>
                        <input type="password" class="form-control password" v-model="input.credential3" name="credential3" required>
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
