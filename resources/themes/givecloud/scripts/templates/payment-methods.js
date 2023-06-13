
theme.PaymentMethodsApp = (function(t){

    function PaymentMethodsApp(selector) {
        this.vm = new Vue({
            el: selector,
            delimiters: ['${', '}'],
            data: {
                input: {
                    payment_type:           Givecloud.Gateway.getDefaultPaymentType(),
                    billing_title:          theme.dataGet(Givecloud.config.account, 'billing_address.title'),
                    billing_first_name:     theme.dataGet(Givecloud.config.account, 'billing_address.first_name'),
                    billing_last_name:      theme.dataGet(Givecloud.config.account, 'billing_address.last_name'),
                    billing_email:          theme.dataGet(Givecloud.config.account, 'billing_address.email'),
                    billing_address1:       theme.dataGet(Givecloud.config.account, 'billing_address.address1'),
                    billing_address2:       theme.dataGet(Givecloud.config.account, 'billing_address.address2'),
                    billing_company:        theme.dataGet(Givecloud.config.account, 'billing_address.company'),
                    billing_city:           theme.dataGet(Givecloud.config.account, 'billing_address.city'),
                    billing_province_code:  theme.dataGet(Givecloud.config.account, 'billing_address.province_code'),
                    billing_zip:            theme.dataGet(Givecloud.config.account, 'billing_address.zip'),
                    billing_country_code:   theme.dataGet(Givecloud.config.account, 'billing_address.country_code', Givecloud.config.billing_country_code),
                    billing_phone:          theme.dataGet(Givecloud.config.account, 'billing_address.phone'),
                    use_as_default:         true,
                },
                payment: {
                    currency: Givecloud.config.currency.code
                },
                form_validated: false
            },
            mounted: function() {
                var self = this;

                jQuery(document).on('gc-number-type', function(event){
                    self.$set(self.payment, 'number_type', event.detail);
                });
            },
            watch: {
                'input.billing_country_code': function(new_value, old_value) {
                    if (new_value !== old_value) {
                        this.input.billing_province_code = null;
                    }
                },
                'input.payment_type': function(new_value, old_value) {
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
                        this.payment.payment_method = t.data_get(this.account, 'payment_methods.0.id');
                    } else {
                        delete this.payment.payment_method;
                    }
                },
                'payment.number': function(new_value, old_value) {
                    if (new_value !== old_value) {
                        this.payment.number_type = Givecloud.CardholderData.getNumberType(new_value);
                    }
                },
                'payment.exp': function(new_value, old_value) {
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
                account: function() {
                    return Givecloud.config.account;
                },
                config: function() {
                    return Givecloud.config;
                },
                gateway: function() {
                    return Givecloud.PaymentTypeGateway(this.input.payment_type);
                },
                gocardless_available: function() {
                    if (Givecloud.config.gateways.bank_account === 'gocardless') {
                        return true;
                    }
                },
                paypal_available: function() {
                    var gateway = Givecloud.PaymentTypeGateway('paypal');
                    if (gateway && gateway.referenceTransactions) {
                        return true;
                    }
                },
                billing_province_required: function() {
                    if (this.billing_subdivisions && Object.keys(this.billing_subdivisions).length) return 'required';
                },
            },
            asyncComputed: {
                countries: {
                    get: function() {
                        return Givecloud.Services.Locale.countries().then(function(data) {
                            delete data.countries['CA'];
                            delete data.countries['US'];
                            return Promise.resolve(data.countries);
                        });
                    },
                    default: {}
                },
                billing_subdivisions: {
                    get: function() {
                        return Givecloud.Services.Locale.subdivisions(this.input.billing_country_code).then(function(data) {
                            if (Sugar.Object.size(data.subdivisions)) {
                                return Promise.resolve(data.subdivisions);
                            } else {
                                return Promise.resolve(null);
                            }
                        });
                    },
                    default: null
                }
            },
            methods: {
                fillAddressFields: function (address) {
                    this.input.billing_address1 = address.line1;
                    this.input.billing_address2 = address.line2;
                    this.input.billing_city = address.city;
                    this.input.billing_zip = address.zip;
                    this.input.billing_country_code = address.country_code;

                    // Deal with province/state dropdown differently as it is populated by an asyncComputed property.
                    // I extracted it to a function to reuse it here, wrapped in a promise
                    // to be able to select the correct province / state.
                    this.getCountryStates(this.input.billing_country_code).then(function (states) {
                        // Match with code.
                        var stateCode = states[address.state_code] ? address.state_code : null;

                        // No match with code, so try to match with name instead.
                        if (! stateCode) {
                            stateCode = Object.keys(states).filter(function (code) {
                                return states[code] === address.state;
                            })[0] || null;
                        }

                        this.input.billing_province_code = stateCode;
                    }.bind(this));
                },
                getCountryStates: function (country) {
                    return Givecloud.Services.Locale.subdivisions(country)
                        .then(function(data) {
                            if (Sugar.Object.size(data.subdivisions)) {
                                this.billing_province_label = theme.trans('general.subdivision_types.' + Sugar.String.underscore(data.subdivision_type));
                                return Promise.resolve(data.subdivisions);
                            }

                            return Promise.resolve(null);
                        }.bind(this));
                },
                supportedCardType: function(type) {
                    return Givecloud.config.supported_cardtypes.indexOf(type) !== -1;
                },
                supportedPaymentType: function(type) {
                    var gateway = Givecloud.PaymentTypeGateway(type);
                    if (type === 'bank_account') {
                        return gateway && gateway.canMakeAchPayment(this.input.currency_code);
                    }
                    if (t.data_get(this.cart, 'requires_ach')) {
                        return false;
                    }
                    return !!gateway;
                },
                paymentExpiry: function(event) {
                    if (event.isTrusted && event.target === document.activeElement) {
                        setTimeout(function() {
                            var position = event.target.value.length;
                            event.target.setSelectionRange(position, position);
                        },60);
                    }
                },
                submitAddPaymentMethodForm: function(event) {
                    this.$validator.validateAll()
                        .then(function(result) {
                            if (result) {
                                this.doAddPaymentMethodRequest();
                            } else {
                                this.form_validated = true;
                            }
                        }.bind(this));
                },
                doAddPaymentMethodRequest: function() {
                    theme.ladda.start(this.$refs.submitBtn, [
                        '<div class="spinner">',
                            '<div class="bounce1"></div>',
                            '<div class="bounce2"></div>',
                            '<div class="bounce3"></div>',
                        '</div>',
                        '<h3>' + theme.trans('scripts.templates.please_wait_while_your_payment_method_is_added') + '</h3>',
                    ]);
                    return Givecloud.Account.PaymentMethods.create(this.input)
                        .then(function(payment_method) {
                            payment_method.payment_type = this.input.payment_type;
                            this.payment_method = payment_method;
                            this.payment.name = this.payment.name || this.payment_method.billing_address.name;
                            this.payment.address_line1 = this.payment_method.billing_address.address1;
                            this.payment.address_line2 = this.payment_method.billing_address.address2;
                            this.payment.address_city = this.payment_method.billing_address.city;
                            this.payment.address_state = this.payment_method.billing_address.province_code;
                            this.payment.address_zip = this.payment_method.billing_address.zip;
                            this.payment.address_country = this.payment_method.billing_address.country_code;
                            return Givecloud.Account.PaymentMethods.tokenize(this.payment_method, this.payment, this.input.payment_type);
                        }.bind(this)).then(function(data) {
                            top.location.reload();
                        }).catch(function(data) {
                            theme.ladda.stop(this.$refs.submitBtn);
                            this.error = theme.error(data);
                            this.$nextTick(function() {
                                theme.scrollIntoView(this.$refs.error, 50);
                            });
                        }.bind(this));
                }
            },
            filters: {
                money: function(value) {
                    return theme.money(value);
                },
                downcase: function(value) {
                    return typeof value === 'string' ? value.toLowerCase() : '';
                }
            }
        });
    }

    if (document.querySelectorAll('#payment-methods-app').length) {
        return new PaymentMethodsApp('#payment-methods-app');
    }

})(theme);
