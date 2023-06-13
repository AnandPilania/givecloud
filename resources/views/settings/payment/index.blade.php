
@extends('layouts.app')
@section('title', 'Payment Gateways')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Payment Gateways
        </h1>
    </div>
</div>


<div id="settings-payment" class="row" v-cloak>
    <div class="col-md-12 col-lg-10 col-lg-offset-1">

        @if (partner() !== 'ps' && partner() !== 'tp' && partner() !== 'pp')
            <div v-if="show_recommended" class="recommended panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-8">
                            <h4 class="text-lg font-bold mb-1">Accept credit/debit cards and ACH with no setup or monthly fees</h4>
                            <p>
                                Enjoy pre-negotiated rates that can decrease as you grow, as low as 2.2% and $.30 per card transaction.* Plus fast payouts.
                                To get started click on the button below and connect your Stripe account. If you don't have a Stripe account, you'll be prompted to create an account.
                            </p>
                        </div>
                        <div class="col-sm-4 text-right">
                            <strong>Recommended</strong>
                            <div class="acceptedLogos">
                                <img src="/jpanel/assets/images/payment/visa.svg" alt="visa">
                                <img src="/jpanel/assets/images/payment/mastercard.svg" alt="mastercard">
                                <img src="/jpanel/assets/images/payment/amex.svg" alt="amex">
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top:30px;">
                        <div class="col-sm-8">
                            <a class="connect" href="{{ $gateways['stripe']->link }}">
                                <img src="/jpanel/assets/images/payment/connect-with-stripe.png" alt="Connect with Stripe">
                            </a>
                        </div>
                        <div class="col-sm-4 text-right">
                            <img class="recommendedLogo" src="/jpanel/assets/images/payment/stripe.svg" alt="stripe">
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="panel panel-default">
            <div class="panel-heading">Smart Routing</div>
            <div class="panel-body">
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label class="control-label">Default Card Gateway</label>
                        <select class="form-control" v-model="credit_card_provider" @change="updateDefaultGateways">
                            <option v-if="credit_card_gateways.length==0" value="">(None)</option>
                            <option v-for="gateway in credit_card_gateways" :value="gateway.provider">${ gateway.name }</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-3">
                        <label class="control-label">Default Bank Gateway</label>
                        <select class="form-control" v-model="bank_account_provider" @change="updateDefaultGateways">
                            <option value="" disabled>(None)</option>
                            <option v-for="gateway in bank_account_gateways" :value="gateway.provider">${ gateway.name }</option>
                        </select>
                    </div>
                    <?php if (feature('kiosks')): ?>
                        <div class="form-group col-sm-3">
                            <label class="control-label">Kiosk Gateway</label>
                            <select class="form-control" v-model="kiosk_provider" @change="updateDefaultGateways">
                                <option value="" disabled>(None)</option>
                                <option v-for="gateway in kiosk_gateways" :value="gateway.provider">${ gateway.name }</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="online-gateways panel panel-default">
            <div class="panel-heading">Configuration</div>
            <ul class="list-group">
                <li v-for="gateway in gateways" class="list-group-item" :class="{ setup: gateway.setup && gateway.enabled }">
                    <div class="row">
                        <div class="col-sm-8">
                            <h4 class="mt-3">${ gateway.name }</h4>
                        </div>
                        <div class="col-sm-4 text-right">
                            <template v-if="gateway.setup">
                                <toggle-button @change="updateEnabled(gateway, $event)" :width="50" :height="20" :value="gateway.enabled" style="margin:0 8px 0 0;"></toggle-button>
                            </template>
                            <a class="btn btn-primary btn-outline" :href="'/jpanel/settings/payment/' + gateway.id">
                                <template v-if="gateway.setup">Edit</template>
                                <template v-else>Set up</template>
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

    </div>
</div>

<script>
spaContentReady(function($) {
    new Vue({
        el: '#settings-payment',
        delimiters: ['${', '}'],
        data: {
            credit_card_provider: <?= dangerouslyUseHTML(json_encode($creditCardProvider)) ?>,
            bank_account_provider: <?= dangerouslyUseHTML(json_encode($bankAccountProvider)) ?>,
            kiosk_provider: <?= dangerouslyUseHTML(json_encode($kioskProvider)) ?>,
            credit_card_providers: <?= dangerouslyUseHTML(json_encode(\Ds\Domain\Commerce\Models\PaymentProvider::$creditCardProviders)) ?>,
            bank_account_providers: <?= dangerouslyUseHTML(json_encode(\Ds\Domain\Commerce\Models\PaymentProvider::getBankAccountProviders())) ?>,
            kiosk_providers: <?= dangerouslyUseHTML(json_encode(\Ds\Domain\Commerce\Models\PaymentProvider::$kioskProviders)) ?>,
            gateways: <?= dangerouslyUseHTML($gateways->values()->toJson()) ?>
        },
        computed: {
            credit_card_gateways: function() {
                var self = this;
                return this.gateways.filter(function(gateway) {
                    return gateway.enabled && self.credit_card_providers.indexOf(gateway.provider) !== -1;
                })
            },
            bank_account_gateways: function() {
                var self = this;
                return this.gateways.filter(function(gateway) {
                    return gateway.enabled && self.bank_account_providers.indexOf(gateway.provider) !== -1;
                })
            },
            kiosk_gateways: function() {
                var self = this;
                return this.gateways.filter(function(gateway) {
                    return gateway.enabled && self.kiosk_providers.indexOf(gateway.provider) !== -1;
                })
            },
            show_recommended: function() {
                return !this.credit_card_provider || this.credit_card_provider === 'givecloudtest';
            }
        },
        methods: {
            updateDefaultGateways: function() {
                var data = {
                    credit_card_provider: this.credit_card_provider,
                    bank_account_provider: this.bank_account_provider,
                    kiosk_provider: this.kiosk_provider,
                };
                axios.patch('/jpanel/settings/payment', data)
                    .then(function(res) {
                        toastr.success('Default provider has been updated.');
                    });
            },
            updateEnabled: function(gateway, e) {
                var data = {
                    provider: gateway.provider,
                    enabled: e.value
                };
                gateway.enabled = data.enabled;
                axios.post('/jpanel/settings/payment', data)
                    .then(function(res) {
                        if (gateway.enabled) {
                            toastr.success('Payment provider has been enabled.');
                        } else {
                            toastr.success('Payment provider has been disabled.');
                        }
                    });
                var updateDefaultGateway = false;
                if (gateway.provider === this.credit_card_provider) {
                    updateDefaultGateway = true;
                    this.credit_card_provider = this.credit_card_gateways.length ? this.credit_card_gateways[0].provider : '';
                }
                if (gateway.provider === this.bank_account_provider) {
                    updateDefaultGateway = true;
                    this.bank_account_provider = this.bank_account_gateways.length ? this.bank_account_gateways[0].provider : '';
                }
                if (updateDefaultGateway) {
                    this.updateDefaultGateways();
                }
            }
        }
    });
});
</script>
@endsection
