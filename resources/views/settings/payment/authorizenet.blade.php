
@extends('layouts.app')
@section('title', 'Payment Gateways')

@section('content')
<div id="settings-payment-app" v-cloak>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Authorize.Net

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
                    To integrate Authorize.Net into your store you need to follow a few simple steps, which are shown below:

                    <span class="text-info">
                        <i class="fa fa-question-circle"></i>
                        <a href="https://account.authorize.net/Activation/Boarding/SignupWithProfile?resellerId=13567&resellerProfileID=9" target="_blank">Register for an Authorize.Net merchant account here</a>
                    </span>
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
                            API Login ID
                            <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="The API Login ID that was supplied when your Authorize.Net account was created."></i>
                        </label>
                        <input type="text" class="form-control" v-model="input.credential1" name="credential1" required>
                    </div>

                    <div class="form-group has-feedback">
                        <label>
                            Transaction Key
                        </label>
                        <input type="password" class="form-control password" v-model="input.credential2" name="credential2" required>
                        <i class="glyphicon glyphicon-eye-open form-control-feedback"></i>
                    </div>

                    <div class="form-group">
                        <label>
                            Public Client Key
                        </label>
                        <input type="text" class="form-control" v-model="input.credential3" name="credential3" required>
                    </div>

                    <div class="form-group has-feedback">
                        <label>
                            Signature Key
                            <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="This is used to verify webhook notifications from Authorize.Net."></i>
                        </label>
                        <input type="password" class="form-control password" v-model="input.credential4" name="credential2" required>
                        <i class="glyphicon glyphicon-eye-open form-control-feedback"></i>
                    </div>

                    <div class="form-group">
                        <label>
                            Test Mode
                            <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="A test account can be created at http://developer.authorize.net/testaccount/."></i>
                        </label>
                        <select class="form-control" v-model="input.test_mode" name="test_mode">
                            <option :value="false">No (Recommended)</option>
                            <option :value="true">Yes</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>
                            Duplicate Window
                            <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="The window of time where duplicate transactions are disallowed."></i>
                        </label>
                        <select class="form-control" v-model="input.duplicate_window" name="duplicate_window" disabled>
                            <option value="0">0 min</option>
                            <option value="1200">20 mins</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>
                            Allow ACH
                        </label>&nbsp;&nbsp;&nbsp;
                        <toggle-button v-model="input.is_ach_allowed"></toggle-button>
                    </div>

                    <div class="form-group">
                        <vue-ladda class="btn btn-success" @click="updateProvider" :loading="saving" data-style="expand-left" data-color="green">
                            <strong>Update information</strong>
                        </vue-ladda>
                    </div>

                </div>
                <div class="col-md-5 col-md-offset-1">

                    <span class="text-info" style="margin-top:20px">
                        <i class="fa fa-question-circle"></i> <strong>Where do I find the Transaction Key?</strong><br>
                        <ol>
                            <li><a href="https://secure.authorize.net" target="_blank">Log in</a> to your Authorize.net account</li>
                            <li>Generate a transaction key from the Settings -&gt; Security -&gt; Obtain Transaction Key link</li>
                        </ol>
                    </span>

                    <span class="text-info">
                        <i class="fa fa-question-circle"></i> <strong>How do I send test transactions?</strong><br>
                        <ol>
                            A test account can be created at <a href="http://developer.authorize.net/testaccount/">http://developer.authorize.net/testaccount/</a>.
                        </ol>
                    </span>

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
