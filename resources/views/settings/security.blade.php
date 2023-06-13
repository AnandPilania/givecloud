
@extends('layouts.app')
@section('title', 'Security')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Security

            <div class="pull-right">
                <a href="https://help.givecloud.com/en/articles/3508435-carding-bots-preventing-fraud" target="_blank" class="btn btn-default" rel="noreferrer"><i class="fa fa-book"></i> Learn More</a>
            </div>
        </h1>
    </div>
</div>


<div id="settings-security" class="row" v-cloak>
    <div class="col-md-12 col-lg-10 col-lg-offset-1">

        <div class="panel panel-default">
            <div class="panel-heading">Checkout Security Measures</div>
            <div class="panel-body" style="padding-top:0">
                <div class="row row-divider" style="padding-top:25px;background:#ffe5e5">
                    <div class="col-md-12">

                        <i class="fa fa-exclamation-circle" style="font-size:80px;color:#ab4242;float:left;margin-right:20px;"></i>

                        <div class="form-group" style="margin-top:4px;margin-bottom:0;max-width:540px">
                            <label>
                                Stop accepting payments
                            </label>&nbsp;&nbsp;&nbsp;
                            <toggle-button v-model="input.public_payments_disabled"></toggle-button>
                            <p class="help-block">
                                This only applies to "public" payments made using a credit card. This won't affect your ability to process payments using the POS.
                            </p>
                            @if (sys_get('public_payments_disabled') && $paymentsDisabledUntil)
                                <div class="mt-6">Payments will automatically resume <strong>{{ $paymentsDisabledUntil->toLocal()->toFdatetimeFormat() }}</strong>.</div>
                            @endif
                        </div>

                    </div>
                </div>
                <div class="row row-divider" style="margin-top:15px">
                    <div class="col-md-5">

                        <div class="form-group" style="margin-top:20px;max-width:450px">
                            <label>
                                Require CAPTCHA
                            </label>
                            <select class="form-control" v-model="input.ss_auth_attempts" name="ss_auth_attempts">
                                <option :value="0">Always</option>
                                <option :value="1">After 1 Failed Attempt</option>
                                <option :value="2">After 2 Failed Attempts</option>
                                <option :value="3">After 3 Failed Attempts</option>
                            </select>
                            <p class="help-block" style="margin-bottom:0">
                                While actively being targeted by Carding Bots always requiring a CAPTCHA is your best option to mitigate against the bots.
                            </p>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="captcha_type" v-model="input.captcha_type" value="recaptcha"> Google reCAPTCHA
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="captcha_type" v-model="input.captcha_type" value="hcaptcha"> hCaptcha
                                </label>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-5 col-md-offset-1">

                        <div class="form-group" style="margin-top:45px;max-width:450px">
                            <label>
                                Require Billing Country to match IP Country
                            </label>&nbsp;&nbsp;&nbsp;
                            <toggle-button v-model="input.require_ip_country_match"></toggle-button>
                            <p class="help-block" style="margin-bottom:0">
                                <u>Warning:</u> This will also block some legitimate payments. For example, someone making a donation while on vaction in another country, etc.
                            </p>
                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-5">

                        <div class="form-group" style="margin-top:25px;max-width:250px">
                            <label>
                                Minimum payment amount
                            </label>
                            <div class="input-group">
                                <div class="input-group-addon"><?= e(currency()->symbol) ?></div>
                                <input type="number" class="form-control" step="0.00" v-model="input.checkout_min_value" min="0">
                            </div>
                            <p class="help-block">
                                Payments below this threshold will not be accepted. This only applies to contributions requiring payment.
                            </p>
                        </div>

                    </div>
                    <div class="col-md-5 col-md-offset-1">



                    </div>
                    <div class="col-md-12">

                        <div class="form-group" style="padding-top:20px">
                            <vue-ladda class="btn btn-success" @click="save" :loading="saving" data-style="expand-left" data-color="green">
                                <strong>Save settings</strong>
                            </vue-ladda>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">Authorization Rate Monitoring</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="clearfix text-right">
                            <select class="form-control input-sm d-inline-block" v-model="chart_window" style="width:180px;margin-bottom:8px">
                                <option :value="15">15min &mdash; The Past 15 Minutes</option>
                                <option :value="60">1h &mdash; The Past Hour</option>
                                <option :value="240">4h &mdash; The Past 4 Hours</option>
                                <option :value="1440">1d &mdash; The Past Day</option>
                                <option :value="2880">2d &mdash; The Past 2 Days</option>
                                <option :value="10080">1w &mdash; The Past Week</option>
                                <option :value="40320">1m &mdash; The Past Month</option>
                            </select>
                        </div>
                        <div id="arm-chart">
                            <canvas ref="arm_canvas"></canvas>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <h2 class="panel-sub-heading" style="margin-top:-15px">1. Set alert conditions</h2>
                    <div class="col-md-12">
                        <div>
                            Trigger when Authorization Rate is below
                            <select class="form-control input-sm d-inline-block" v-model="input.arm_rate_threshold" style="width:70px;margin:0 4px;">
                                <option :value="0.05">5%</option>
                                <option :value="0.1">10%</option>
                                <option :value="0.15">15%</option>
                                <option :value="0.2">20%</option>
                                <option :value="0.25">25%</option>
                                <option :value="0.3">30%</option>
                                <option :value="0.35">35%</option>
                                <option :value="0.4">40%</option>
                                <option :value="0.45">45%</option>
                                <option :value="0.5">50%</option>
                                <option :value="0.55">55%</option>
                                <option :value="0.6">60%</option>
                                <option :value="0.65">65%</option>
                                <option :value="0.7">70%</option>
                                <option :value="0.75">75%</option>
                                <option :value="0.8">80%</option>
                                <option :value="0.85">85%</option>
                                <option :value="0.9">90%</option>
                                <option :value="0.95">95%</option>
                            </select>
                            during the last
                            <select class="form-control input-sm d-inline-block" v-model="input.arm_evaluation_window" style="width:100px;margin:0 4px;">
                                <option :value="5">5 minutes</option>
                                <option :value="10">10 minutes</option>
                                <option :value="15">15 minutes</option>
                                <option :value="30">30 minutes</option>
                                <option :value="45">45 minutes</option>
                                <option :value="60">60 minutes</option>
                            </select>
                            with at least
                            <input type="number" class="form-control input-sm d-inline-block" v-model="input.arm_attempt_threshold" min="10" style="width:50px;margin:0 4px;">
                            attempts.
                        </div>
                    </div>
                </div>
                <div class="row">
                    <h2 class="panel-sub-heading">2. Take immediate action</h2>
                    <div class="col-md-12">
                        <div>
                            <select class="form-control input-sm d-inline-block" v-model="input.arm_immediate_action" style="width:280px;margin-right:4px;">
                                <option value="none">[No action]</option>
                                <option value="always_require_captcha">Always require CAPTCHA (Recommended)</option>
                                <option value="stop_accepting_payments">Stop accepting payments</option>
                            </select>
                            after an alert has been triggered.
                        </div>
                    </div>
                </div>
                <div class="row row-divider">
                    <h2 class="panel-sub-heading">3. Notify your team</h2>
                    <div class="col-md-12">
                        <div class="alert alert-warning d-inline-block">
                            All users who are account owners will be included in the notifications regardless of whether they are specified below.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div>
                            <span>
                                <vue-selectize v-model="input.arm_recipients" :settings="recipients" class="form-control auto-height" placeholder="Select recipients(s) or enter new address..." multiple></vue-selectize>
                            </span>
                            <select class="form-control input-sm d-inline-block" v-model="input.arm_renotify_recipients" style="width:150px;margin-right:4px;">
                                <option :value="0">[Never]</option>
                                <option :value="10">Every 10 minutes</option>
                                <option :value="20">Every 20 minutes</option>
                                <option :value="30">Every 30 minutes</option>
                                <option :value="40">Every 40 minutes</option>
                                <option :value="50">Every 50 minutes</option>
                                <option :value="60">Every 60 minutes</option>
                                <option :value="90">Every 90 minutes</option>
                                <option :value="120">Every 2 hours</option>
                                <option :value="180">Every 3 hours</option>
                                <option :value="240">Every 4 hours</option>
                                <option :value="300">Every 5 hours</option>
                                <option :value="360">Every 6 hours</option>
                                <option :value="720">Every 12 hours</option>
                                <option :value="1440">Every 24 hours</option>
                            </select>
                            renotify if the monitor has not been resolved.
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group" style="padding-top:20px">
                            <vue-ladda class="btn btn-success" @click="save" :loading="saving" data-style="expand-left" data-color="green">
                                <strong>Save settings</strong>
                            </vue-ladda>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">Two Factor Authentication</div>
            <div class="panel-body" style="padding-top:0">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group" style="margin-top:20px;max-width:450px">
                            <label>
                                Rollout strategy
                            </label>
                            <select class="form-control" v-model="input.two_factor_authentication" name="two_factor_authentication">
                                <option value="optional">OPTIONAL (Users can enable 2FA at their own discretion)</option>
                                <option value="prompt">PROMPT (Nag users about enabling 2FA after they login)</option>
                                <option value="force">FORCE (Don't allow users to do anything until 2FA is enabled)</option>
                            </select>
                            <p class="help-block" style="margin-bottom:0">
                                Pick the rollout strategy that works best for your organization.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group" style="padding-top:10px">
                            <vue-ladda class="btn btn-success" @click="save" :loading="saving" data-style="expand-left" data-color="green">
                                <strong>Save settings</strong>
                            </vue-ladda>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
spaContentReady(function($) {
    new Vue({
        el: '#settings-security',
        delimiters: ['${', '}'],
        data: {
            chart: null,
            chart_window: 240,
            input: {!! json_encode([
                'two_factor_authentication' => sys_get('two_factor_authentication'),
                'captcha_type'             => sys_get('captcha_type'),
                'ss_auth_attempts'         => sys_get('int:ss_auth_attempts'),
                'public_payments_disabled' => sys_get('bool:public_payments_disabled'),
                'require_ip_country_match' => sys_get('bool:require_ip_country_match'),
                'checkout_min_value'       => sys_get('double:checkout_min_value'),
                'arm_rate_threshold'       => sys_get('double:arm_rate_threshold'),
                'arm_evaluation_window'    => sys_get('int:arm_evaluation_window'),
                'arm_attempt_threshold'    => sys_get('int:arm_attempt_threshold'),
                'arm_immediate_action'     => sys_get('arm_immediate_action'),
                'arm_recipients'           => sys_get('list:arm_recipients'),
                'arm_renotify_recipients'  => sys_get('int:arm_renotify_recipients'),
            ]) !!},
            recipients: {!! json_encode([
                'create' => true,
                'persist' => true,
                'createOnBlur' => true,
                'options' => \DB::table('user')
                    ->where('is_account_admin', false)
                    ->orderBy('email')
                    ->pluck('email')
                    ->merge(sys_get('list:arm_recipients'))
                    ->sort()
                    ->map(function ($email) {
                        return ['text' => $email, 'value' => $email];
                    }),
                'plugins' => ['remove_button'],
            ]) !!},
            saving: false,
        },
        mounted: function() {
            var ctx = this.$refs.arm_canvas.getContext('2d');
            ctx.canvas.width = 1000;
            ctx.canvas.height = 110;
            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartData['labels']) !!},
                    datasets: [{
                        label: 'Failed',
                        backgroundColor: '#f05146',
                        data: {!! json_encode($chartData['dataset1']) !!}
                    },{
                        label: 'Succeeded',
                        backgroundColor: '#5cdb71',
                        data: {!! json_encode($chartData['dataset2']) !!}
                    }]
                },
                options: {
                    legend: {
                        display: false
                    },
                    animation: {
                        duration: 0
                    },
                    scales: {
                        x: {
                            stacked: true,
                            offset: true,
                            gridLines: {
                                drawOnChartArea: false,
                                tickMarkLength: 6
                            },
                            ticks: {
                                major: {
                                    enabled: true
                                },
                                autoSkip: true,
                                autoSkipPadding: 75,
                                maxRotation: 0,
                                padding: 4,
                                fontSize: 11,
                                fontColor: '#999'
                            }
                        },
                        y: {
                            stacked: true,
                            gridLines: {
                                drawBorder: false,
                                drawTicks: false
                            },
                            ticks: {
                                beginAtZero: true,
                                maxTicksLimit: 5,
                                padding: 8,
                                fontSize: 11,
                                fontColor: '#999',
                                callback: function(value, index, values) {
                                    return Sugar.Number.abbr(value, 1);
                                }
                            }
                        }
                    }
                }
            });
        },
        watch: {
            chart_window: function(new_value, old_value) {
                if (new_value !== old_value) {
                    var chart = this.chart;
                    axios.get('/jpanel/settings/security/payments.json', { params: { minutes: new_value } })
                        .then(function(res) {
                            chart.config.data.labels = res.data.labels;
                            chart.config.data.datasets[0].data = res.data.dataset1;
                            chart.config.data.datasets[1].data = res.data.dataset2;
                            chart.update();
                        });
                }
            }
        },
        methods: {
            save: function() {
                axios.post('/jpanel/settings/security', this.input)
                    .then(function(res) {
                        toastr.success('Security settings have been updated.');
                    }).catch(function(res) {
                        toastr.error(res.response.data);
                    });
            },
        }
    });
});
</script>
@endsection
