<template>
    <div v-cloak>
        <div v-if="screen == 'finished' && receipt" class="pos-payment-complete text-center" style="margin: 60px 0">
            <i class="fa fa-check-circle fa-5x text-success"></i>
            <h1>Contribution #{{ receipt.invoicenumber }} Complete!</h1>
            <p>
                <a
                    :href="'/jpanel/contributions/' + receipt.id + '/edit'"
                    target="_blank"
                    class="btn btn-info btn-lg view-finish-btn"
                    ><i class="fa fa-search"></i> View</a
                >&nbsp;
                <a
                    :href="'/jpanel/contributions/packing_slip?print&id=' + receipt.id"
                    target="_blank"
                    class="btn btn-info btn-lg print-finish-btn"
                    ><i class="fa fa-print"></i> Print</a
                >&nbsp;
                <a href="#" class="btn btn-default btn-lg finish-btn" data-dismiss="modal">Close</a>
            </p>
        </div>

        <div v-else-if="screen == 'loading'" class="pos-payment-processing text-placeholder text-center">
            <i class="fa fa-spin fa-circle-o-notch fa-4x"></i>
            <h1>Processing Payment</h1>
        </div>

        <div v-show="screen === 'pay'" class="pos-payment-wrap">
            <div class="modal-body">
                <a href="#" data-dismiss="modal" class="pull-right"><i class="fa fa-times"></i></a>

                <div v-if="no_payment_required" class="pos-payment-not-required text-placeholder text-center">
                    <i class="fa fa-money fa-4x"></i>
                    <h1>No Payment Required</h1>
                </div>
                <div v-else class="pos-payment-options">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs">
                        <template v-if="config.gateways.credit_card">
                            <li
                                v-if="paysafe_checkout_available"
                                :class="{ active: input.payment_type == 'credit_card' }"
                                role="presentation"
                            >
                                <a @click="input.payment_type = 'credit_card'">
                                    <i class="fa fa-credit-card"></i> Credit Card
                                </a>
                            </li>
                            <li v-else :class="{ active: input.payment_type == 'credit_card' }" role="presentation">
                                <a @click="input.payment_type = 'credit_card'">
                                    <i class="fa fa-credit-card"></i> Credit Card
                                </a>
                            </li>
                        </template>
                        <li
                            v-if="supportedPaymentType('bank_account')"
                            :class="{ active: input.payment_type == 'bank_account' }"
                            role="presentation"
                        >
                            <a @click="input.payment_type = 'bank_account'"> <i class="fa fa-bank"></i> Bank </a>
                        </li>
                        <li
                            v-if="payment_methods.length"
                            :class="{ active: input.payment_type == 'payment_method' }"
                            role="presentation"
                        >
                            <a @click="input.payment_type = 'payment_method'">
                                <i class="fa fa-user"></i> My Methods
                            </a>
                        </li>
                        <li :class="{ active: input.payment_type == 'cash' }" role="presentation">
                            <a @click="input.payment_type = 'cash'"> <i class="fa fa-money"></i> Cash </a>
                        </li>
                        <li :class="{ active: input.payment_type == 'check' }" role="presentation">
                            <a @click="input.payment_type = 'check'"> <i class="fa fa-pencil-square-o"></i> Check </a>
                        </li>
                        <li :class="{ active: input.payment_type == 'other' }" role="presentation">
                            <a @click="input.payment_type = 'other'"> Other </a>
                        </li>
                    </ul>

                    <br />

                    <div v-show="input.payment_type == 'credit_card'" class="row">
                        <div v-if="paysafe_checkout_available" class="col-sm-8 col-sm-offset-2">
                            <div class="text-center text-muted">
                                Continue below<br />
                                <i class="fa fa-chevron-down fa-1x"></i>
                            </div>
                        </div>
                        <credit-card v-else class="col-sm-8 col-sm-offset-2">
                            <div
                                class="form-group payment-number-input"
                                :class="{ 'has-errors': errors.has('pay.number') || unsupported_card_type }"
                            >
                                <label for="inputPaymentNumber" class="d-none">Card Number</label>
                                <payment-field field="inputPaymentNumber">
                                    <div class="input-group input-group-lg">
                                        <div class="input-group-addon"><i class="fa fa-credit-card"></i></div>
                                        <the-mask
                                            id="inputPaymentNumber"
                                            type="tel"
                                            class="form-control text-center input-lg monospace"
                                            autofocus
                                            maxlength="19"
                                            placeholder="0000 0000 0000 0000"
                                            mask="#### #### #### ####"
                                            name="number"
                                            v-model="payment.number"
                                            v-validate.initial="'required|credit_card'"
                                            x-autocompletetype="cc-number"
                                            autocompletetype="cc-number"
                                            autocorrect="off"
                                            spellcheck="off"
                                            autocapitalize="off"
                                        ></the-mask>
                                    </div>
                                </payment-field>
                                <p v-if="unsupported_card_type" class="help-block error-text mt-1">
                                    <i class="fa fa-warning"></i>
                                    Unsupported card type.
                                </p>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6" :class="{ 'has-errors': errors.has('pay.exp') }">
                                    <label for="inputPaymentExpiry" class="d-none">MM / YY</label>
                                    <payment-field field="inputPaymentExpiry">
                                        <the-mask
                                            id="inputPaymentExpiry"
                                            type="tel"
                                            class="form-control text-center input-lg monospace"
                                            maxlength="7"
                                            placeholder="MM / YY"
                                            mask="## / ##"
                                            @input.native="paymentExpiry($event)"
                                            name="exp"
                                            v-model="payment.exp"
                                            v-validate.initial="'required|expiration_date'"
                                            x-autocompletetype="cc-exp"
                                            autocompletetype="cc-exp"
                                            autocorrect="off"
                                            spellcheck="off"
                                            autocapitalize="off"
                                        ></the-mask>
                                    </payment-field>
                                </div>
                                <div class="form-group col-sm-6" :class="{ 'has-errors': errors.has('pay.cvv') }">
                                    <label for="inputPaymentCVV" class="d-none">CVV</label>
                                    <payment-field field="inputPaymentCVV">
                                        <the-mask
                                            id="inputPaymentCVV"
                                            type="tel"
                                            class="form-control text-center input-lg monospace"
                                            maxlength="4"
                                            placeholder="CVD"
                                            mask="####"
                                            name="cvv"
                                            v-model="payment.cvv"
                                            v-validate.initial="'required|cvv:' + payment.number_type"
                                            x-autocompletetype="cc-csc"
                                            autocompletetype="cc-csc"
                                            autocorrect="off"
                                            spellcheck="off"
                                            autocapitalize="off"
                                        ></the-mask>
                                        <span class="text-muted">Optional</span>
                                    </payment-field>
                                </div>
                            </div>
                        </credit-card>
                    </div>
                    <div v-show="input.payment_type == 'bank_account'" class="row">
                        <div class="col-sm-8 col-sm-offset-2">
                            <div class="form-group">
                                <label for="inputPaymentAccountHolderName">Account Holder Name</label>
                                <input
                                    id="inputPaymentAccountHolderName"
                                    type="text"
                                    class="form-control text-center"
                                    name="account_holder_name"
                                    v-model="payment.account_holder_name"
                                    v-validate.initial="'required'"
                                />
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <div
                                        class="form-group payment-number-input"
                                        :class="{ 'has-errors': errors.has('pay.account_holder_type') }"
                                    >
                                        <label for="inputPaymentAccountHolderType">Account Holder Type</label>
                                        <select
                                            id="inputPaymentAccountHolderType"
                                            class="form-control"
                                            name="account_holder_type"
                                            v-model="payment.account_holder_type"
                                            v-validate.initial="'required'"
                                            autocomplete="off"
                                        >
                                            <option value="personal">Individual</option>
                                            <option value="business">Company</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div
                                        class="form-group payment-number-input"
                                        :class="{ 'has-errors': errors.has('pay.account_type') }"
                                    >
                                        <label for="inputPaymentAccountType">Account Type</label>
                                        <select
                                            id="inputPaymentAccountType"
                                            class="form-control"
                                            name="account_type"
                                            v-model="payment.account_type"
                                            v-validate.initial="'required'"
                                            autocomplete="off"
                                        >
                                            <option value="checking">Checking</option>
                                            <option value="savings">Savings</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <template v-if="payment.currency == 'CAD'">
                                <div class="row">
                                    <div
                                        class="form-group col-sm-6"
                                        :class="{ 'has-errors': errors.has('pay.transit_number') }"
                                    >
                                        <label for="inputPaymentTransitNumber">Transit #</label>
                                        <the-mask
                                            id="inputPaymentTransitNumber"
                                            type="tel"
                                            class="form-control monospace"
                                            maxlength="5"
                                            placeholder="00000"
                                            mask="#####"
                                            name="transit_number"
                                            v-model="payment.transit_number"
                                            v-validate.initial="'required'"
                                            autocorrect="off"
                                            spellcheck="off"
                                            autocapitalize="off"
                                        ></the-mask>
                                    </div>
                                    <div
                                        class="form-group col-sm-6"
                                        :class="{ 'has-errors': errors.has('pay.institution_number') }"
                                    >
                                        <label for="inputPaymentInstitutionNumber">Institution #</label>
                                        <the-mask
                                            id="inputPaymentInstitutionNumber"
                                            type="tel"
                                            class="form-control monospace"
                                            maxlength="3"
                                            placeholder="000"
                                            mask="###"
                                            name="institution_number"
                                            v-model="payment.institution_number"
                                            v-validate.initial="'required'"
                                            autocorrect="off"
                                            spellcheck="off"
                                            autocapitalize="off"
                                        ></the-mask>
                                    </div>
                                </div>
                                <div class="form-group" :class="{ 'has-errors': errors.has('pay.account_number') }">
                                    <label for="inputPaymentAccountNumber">Account #</label>
                                    <input
                                        id="inputPaymentAccountNumber"
                                        type="number"
                                        class="form-control monospace"
                                        minlength="4"
                                        maxlength="17"
                                        placeholder="0000000"
                                        name="account_number"
                                        v-model="payment.account_number"
                                        v-validate.initial="'required'"
                                        autocorrect="off"
                                        spellcheck="off"
                                        autocapitalize="off"
                                    />
                                </div>
                            </template>
                            <template v-else>
                                <div class="row">
                                    <div
                                        class="form-group col-sm-6"
                                        :class="{ 'has-errors': errors.has('pay.routing_number') }"
                                    >
                                        <label for="inputPaymentRoutingNumber">Routing #</label>
                                        <input
                                            id="inputPaymentRoutingNumber"
                                            type="number"
                                            class="form-control monospace"
                                            placeholder="0000000"
                                            name="routing_number"
                                            v-model="payment.routing_number"
                                            v-validate.initial="'required'"
                                            autocorrect="off"
                                            spellcheck="off"
                                            autocapitalize="off"
                                        />
                                    </div>
                                    <div
                                        class="form-group col-sm-6"
                                        :class="{ 'has-errors': errors.has('pay.account_number') }"
                                    >
                                        <label for="inputPaymentAccountNumber">Account #</label>
                                        <input
                                            id="inputPaymentAccountNumber"
                                            type="number"
                                            class="form-control monospace"
                                            minlength="4"
                                            maxlength="17"
                                            placeholder="0000000"
                                            name="account_number"
                                            v-model="payment.account_number"
                                            v-validate.initial="'required'"
                                            autocorrect="off"
                                            spellcheck="off"
                                            autocapitalize="off"
                                        />
                                    </div>
                                </div>
                            </template>

                            <div class="checkbox">
                                <label>
                                    <input
                                        type="checkbox"
                                        name="ach_agree_tos"
                                        v-model="payment.ach_agree_tos"
                                        v-validate.initial="'required'"
                                    />
                                    <small class="text-muted"
                                        >By completing this purchase, you authorize us to charge the account above for
                                        the amount specified in the Total field.</small
                                    >
                                </label>
                            </div>
                        </div>
                    </div>
                    <template v-if="input.payment_type == 'payment_method'">
                        <template v-if="payment_methods.length > 0">
                            <div
                                v-for="method in payment_methods"
                                :key="method.id"
                                class="radio"
                                :class="{ 'text-bold': method.use_as_default }"
                            >
                                <label>
                                    <input
                                        type="radio"
                                        name="payment_method_id"
                                        v-model="payment.payment_method"
                                        :value="method.id"
                                    />
                                    <i class="fa fa-fw" :class="[method.fa_icon]"></i> {{ method.account_number }}
                                    <template v-if="method.use_as_default">(Default)</template>
                                </label>
                            </div>
                        </template>
                        <div v-else class="pos-payment-not-available text-placeholder text-center">
                            <i class="fa fa-creditcard fa-4x"></i>
                            <h1>No Payment Methods Available</h1>
                        </div>
                    </template>
                    <template v-else-if="input.payment_type == 'cash'">
                        <div class="pos-cash-wrap clearfix">
                            <div class="keypad-totals">
                                <div class="keypad-total-due">
                                    <div class="keypad-total-label col-sm-6">Total Due</div>
                                    <div class="keypad-total-value col-sm-6 text-right pos-cash-total">
                                        {{ order.totalamount | money }}
                                    </div>
                                </div>
                                <div class="keypad-total-paid">
                                    <div class="keypad-total-label col-sm-6">Paid</div>
                                    <input
                                        type="tel"
                                        class="keypad-total-value col-sm-6 text-right pos-cash-received"
                                        autofocus
                                        placeholder="0.00"
                                        v-model="input.cash_received"
                                    />
                                </div>
                                <div class="keypad-total-change">
                                    <div class="keypad-total-label col-sm-6">Change</div>
                                    <div class="keypad-total-value col-sm-6 text-right pos-cash-change">
                                        {{ cash_change | money }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-if="input.payment_type == 'check'">
                        <div class="pos-check-wrap row">
                            <div class="col-sm-8 col-sm-offset-2">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label>Check Number:</label>
                                            <input
                                                class="form-control input-lg pos-check-number"
                                                type="tel"
                                                name="check_number"
                                                autofocus
                                                v-model="input.check_number"
                                                placeholder="00000"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label>Check Date:</label>
                                            <input
                                                class="form-control input-lg date pos-check-date"
                                                type="tel"
                                                ref="check_date"
                                                name="check_date"
                                                v-model="input.check_date"
                                                placeholder="XXX, X XXXX"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label>Check Amount:</label>
                                            <div class="input-group input-group-lg">
                                                <div class="input-group-addon"><i class="fa fa-dollar"></i></div>
                                                <input
                                                    class="form-control text-right input-lg pos-check-amt"
                                                    type="tel"
                                                    name="check_amt"
                                                    v-model="input.check_amt"
                                                    placeholder="0.00"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-if="input.payment_type == 'other'">
                        <div class="pos-other-wrap row">
                            <div class="col-sm-8 col-sm-offset-2">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label>Reference:</label>
                                            <input
                                                class="form-control input-lg pos-payment-other-ref"
                                                autofocus
                                                type="text"
                                                name="payment_other_reference"
                                                v-model="input.payment_other_reference"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label>Note:</label>
                                            <textarea
                                                class="form-control pos-payment-other-note"
                                                name="payment_other_note"
                                                v-model="input.payment_other_note"
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="modal-footer">
                <div class="row text-left">
                    <div class="col-sm-6">
                        <div class="checkbox disabled">
                            <label>
                                <input
                                    type="checkbox"
                                    class="pos-send-confirmations"
                                    v-model="input.send_confirmation_emails"
                                />
                                Send confirmation emails.<br />
                                <small class="text-muted">If email is provided, send all confirmation emails.</small>
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="checkbox" v-if="show_fulfillment">
                            <label>
                                <input type="checkbox" class="pos-mark-completed" v-model="input.mark_as_complete" />
                                Mark contribution as fulfilled.<br />
                                <small class="text-muted">Contribution will be pushed directly to "Fulfilled".</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="screen == 'pay' || screen == 'loading'" class="modal-footer">
            <button
                type="button"
                @click="submitForm($event)"
                class="btn btn-success btn-lg btn-block btn-bold pos-finalize-payment"
                :class="{ disable: screen == 'loading' }"
                :disabled="screen == 'loading'"
            >
                <i v-if="screen == 'loading'" class="fa fa-spin fa-circle-o-notch"></i>
                <i v-else class="fa fa-check"></i>
                Finish
            </button>
        </div>
    </div>
</template>

<script>
import CreditCard from './CreditCard';
import PaymentField from './PaymentField';
import * as jQuery from 'jquery';
import * as Sugar from 'sugar';

function dataGet(data, property, defaultValue) {
    var value = Sugar.Object.get(data, property);
    if (value === '' || value === null || typeof value === 'undefined') {
        return defaultValue;
    } else {
        return value;
    }
}

function dataSet(data, property, value) {
    Sugar.Object.set(data, property, value);
}

function toNumber(number, defaultValue) {
    if (defaultValue === void 0) {
        defaultValue = null;
    }
    if (typeof number === 'number') {
        return number;
    } else if (typeof number === 'string') {
        number = Sugar.String.toNumber(number);
        return isNaN(number) ? defaultValue : number;
    }
    return defaultValue;
}

function formatNumber(number, precision) {
    return Sugar.Number.format(toNumber(number), precision || 2);
}

export default {
    components: {
        'credit-card': CreditCard,
        'payment-field': PaymentField,
    },
    data() {
        return {
            input: {
                payment_type: window.pos.lastPaymentType || Givecloud.Gateway.getDefaultPaymentType(),
                check_number: null,
                check_date: null,
                check_amt: null,
                cash_received: null,
                send_confirmation_emails: true,
                mark_as_complete: false,
            },
            payment: {
                number: '',
                number_type: '',
                exp: '',
                cvv: '',
                account_holder_name: '',
                account_holder_type: 'personal',
                account_type: 'checking',
                routing_number: '',
                account_number: '',
                ach_agree_tos: true,
                save_payment_method: false,
                currency: window.pos.currency.active.code,
            },
            screen: 'pay',
            receipt: null,
        };
    },
    mounted() {
        this.specialFields();
    },
    watch: {
        'input.payment_type': function (new_value, old_value) {
            if (new_value !== 'credit_card') {
                delete this.payment.number;
                delete this.payment.number_type;
                delete this.payment.cvv;
                delete this.payment.exp;
                delete this.payment.save_payment_method;
            }
            if (new_value !== 'bank_account') {
                delete this.payment.account_holder_type;
                delete this.payment.account_type;
                delete this.payment.routing_number;
                delete this.payment.account_number;
                delete this.payment.ach_agree_tos;
                delete this.payment.save_payment_method;
            }
            if (new_value !== 'paypal') {
                delete this.payment.save_payment_method;
            }
            if (new_value === 'payment_method' && !this.payment.payment_method) {
                this.payment.payment_method = dataGet(this.account, 'payment_methods.0.id');
            } else {
                delete this.payment.payment_method;
            }
            setTimeout(() => this.specialFields(), 100);
        },
        'payment.number': function (new_value, old_value) {
            if (new_value !== old_value) {
                this.payment.number_type = Givecloud.CardholderData.getNumberType(new_value);
            }
        },
        'payment.exp': function (new_value, old_value) {
            if (new_value !== old_value && new_value > 1) {
                if (new_value.length === 1) {
                    this.payment.exp = '0' + new_value + ' / ';
                }
                if (new_value.length === 2) {
                    this.payment.exp = new_value + ' / ';
                }
            }
        },
    },
    computed: {
        order() {
            return window.pos.order;
        },
        account() {
            return this.order.member;
        },
        payment_methods() {
            var payment_methods = this.account ? this.account.payment_methods : [];
            return payment_methods.filter((payment_method) => {
                return payment_method.status === 'ACTIVE';
            });
        },
        config() {
            return Givecloud.config;
        },
        gateway() {
            return Givecloud.PaymentTypeGateway(this.input.payment_type);
        },
        paysafe_checkout_available() {
            var gateway = Givecloud.PaymentTypeGateway('credit_card');
            if (Givecloud.config.gateways.credit_card === 'paysafe' && gateway.useCheckout) {
                return this.order.totalamount > 0;
            }
        },
        is_onetime() {
            return this.order.recurring_items == 0;
        },
        has_recurring() {
            return this.order.recurring_items > 0;
        },
        requires_ach() {
            return this.order.items.reduce(function (carry, item) {
                return carry || item.requires_ach;
            });
        },
        requires_payment() {
            return this.order.totalamount > 0 || this.order.recurring_items > 0;
        },
        no_payment_required() {
            return this.requires_payment === false;
        },
        cash_received: function () {
            return toNumber(this.input.cash_received, 0);
        },
        cash_change: function () {
            return this.cash_received - this.order.totalamount;
        },
        unsupported_card_type: function () {
            return this.payment.number_type && !this.supportedCardType(this.payment.number_type);
        },
        show_fulfillment: function () {
            if (window._settings.use_fulfillment === 'always') {
                return true;
            }

            if (window._settings.use_fulfillment === 'never') {
                return false;
            }

            return this.order.shippable_items > 0;
        },
    },
    methods: {
        specialFields() {
            if (this.input.payment_type === 'check') {
                jQuery(this.$refs.check_date)
                    .datepicker({ format: 'M d, yyyy', autoclose: true })
                    .on('changeDate', (e) => {
                        this.$nextTick(() => (this.input.check_date = e.currentTarget.value));
                    });
            }
            jQuery(this.$el).find('input[autofocus]').focus();
        },
        setScreen(screen) {
            this.$nextTick(() => {
                this.screen = screen;
            });
        },
        supportedCardType(type) {
            return Givecloud.config.supported_cardtypes.indexOf(type) !== -1;
        },
        supportedPaymentType(type) {
            const gateway = Givecloud.PaymentTypeGateway(type);
            if (type === 'bank_account') {
                return gateway && gateway.canMakeAchPayment(this.payment.currency);
            }
            if (this.requires_ach) {
                return false;
            }
            return !!gateway;
        },
        paymentExpiry(event) {
            if (event.isTrusted && event.target === document.activeElement) {
                setTimeout(() => {
                    var position = event.target.value.length;
                    event.target.setSelectionRange(position, position);
                }, 60);
            }
        },
        submitForm(event) {
            if (this.input.payment_type === 'credit_card' || this.input.payment_type === 'bank_account') {
                this.$validator.validateAll('pay').then((valid) => {
                    if (valid) {
                        window.pos.payment.finalize(event);
                    }
                });
            } else {
                window.pos.payment.finalize(event);
            }
        },
        resetData() {
            Object.assign(this.$data, this.$options.data.apply(this));
        },
    },
    filters: {
        money(value) {
            return Givecloud.config.currency.symbol + formatNumber(value);
        },
    },
};
</script>
