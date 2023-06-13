
theme.SignupApp = (function(t){

    function SignupApp(selector) {
        this.vm = new Vue({
            el: selector,
            delimiters: ['${', '}'],
            data: {
                step: 'signup',
                account: null,
                login: {
                    email: null,
                    password: null,
                    verify_sms: null,
                },
                signup: {
                    payment_type: Givecloud.Gateway.getDefaultPaymentType(),
                    account_type_id: 1,
                    organization_name: null,
                    title: null,
                    first_name: null,
                    last_name: null,
                    email: null,
                    address1: null,
                    address2: null,
                    city: null,
                    state: null,
                    zip: null,
                    country: null,
                    phone: null,
                    password: null,
                    email_opt_in: null,
                    referral_source: null,
                    verify_sms: null,
                    'g-recaptcha-response': null,
                },
                payment: {
                    currency: Givecloud.config.currency.code,
                },
                error: null,
                login_validated: false,
                signup_validated: false,
                referral_source: null,
            },
            mounted: function() {
                this.login.verify_sms = this.$refs.verify_sms.value;
                this.signup.verify_sms = this.$refs.verify_sms.value;
                this.signup['g-recaptcha-response'] = this.$refs.recaptcha_token.value;
                if (this.$refs.referral_source) {
                    this.signup.referral_source = this.$refs.referral_source.value;
                }
                var self = this;
                jQuery(document).on('gc-number-type', function(event){
                    self.$set(self.payment, 'number_type', event.detail);
                });
            },
            watch: {
                referral_source: function(new_value, old_value) {
                    var self = this;
                    if (new_value !== old_value) {
                        if (new_value === 'other') {
                            this.signup.referral_source = null;
                            this.$nextTick(function(){
                                self.$refs.referral_source_other.focus();
                            });
                        } else {
                            this.signup.referral_source = new_value;
                        }
                    }
                },
                'signup.country': function(new_value, old_value) {
                    if (new_value !== old_value) {
                        this.signup.zip = null;
                    }
                },
                'signup.payment_type': function(new_value, old_value) {
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
                subdivisions: {
                    get: function() {
                        if (!this.signup.country) {
                            return Promise.resolve(null);
                        }
                        return Givecloud.Services.Locale.subdivisions(this.signup.country).then(function(data) {
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
            computed: {
                account_type: function() {
                    return this.account_types.find(function(type) {
                        return type.id == this.signup.account_type_id;
                    }.bind(this));
                },
                account_types: function() {
                    return Givecloud.config.account_types;
                },
                config: function() {
                    return Givecloud.config;
                },
                donor_title_options: function() {
                    return Givecloud.config.title_options;
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
                payment_method: function() {
                    return {
                        payment_type: this.signup.payment_type,
                        billing_company: this.signup.organization_name,
                        billing_title: this.signup.title,
                        billing_first_name: this.signup.first_name,
                        billing_last_name: this.signup.last_name,
                        billing_email: this.signup.email,
                        billing_address1: this.signup.address1,
                        billing_address2: this.signup.address2,
                        billing_city: this.signup.city,
                        billing_province_code: this.signup.state,
                        billing_zip: this.signup.zip,
                        billing_country_code: this.signup.country,
                        billing_phone: this.signup.phone,
                        use_as_default: true,
                    };
                },
                referral_sources: function() {
                    return Givecloud.config.referral_sources;
                },
            },
            methods: {
                supportedCardType: function(type) {
                    return Givecloud.config.supported_cardtypes.indexOf(type) !== -1;
                },
                supportedPaymentType: function(type) {
                    var gateway = Givecloud.PaymentTypeGateway(type);
                    if (type === 'bank_account') {
                        return gateway && gateway.canMakeAchPayment(this.payment.currency);
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
                submitLoginForm: function(event) {
                    this.$validator.validateAll('login')
                        .then(function(result) {
                            if (result) {
                                this.doLoginRequest();
                            } else {
                                this.login_validated = true;
                            }
                        }.bind(this));
                },
                doLoginRequest: function() {
                    theme.ladda.start(this.$refs.loginBtn, [
                        '<div class="spinner">',
                            '<div class="bounce1"></div>',
                            '<div class="bounce2"></div>',
                            '<div class="bounce3"></div>',
                        '</div>',
                        '<h3>' + theme.trans('scripts.templates.signup.please_wait_while_we_login_to_your_account') + '</h3>',
                    ]);
                    return Givecloud.Account.login(this.login)
                        .then(function(data) {
                            top.location.reload();
                        }).catch(function(data) {
                            theme.ladda.stop(this.$refs.loginBtn);
                            this.error = theme.error(data);
                            this.$nextTick(function() {
                                theme.scrollIntoView(this.$refs.error, 50);
                            });
                        }.bind(this));
                },
                submitSignupForm: function(event) {
                    this.$validator.validateAll('signup')
                        .then(function(result) {
                            if (result) {
                                this.doSignupRequest();
                            } else {
                                this.signup_validated = true;
                            }
                        }.bind(this));
                },
                doSignupRequest: function() {
                    theme.ladda.start(this.$refs.submitBtn, [
                        '<div class="spinner">',
                            '<div class="bounce1"></div>',
                            '<div class="bounce2"></div>',
                            '<div class="bounce3"></div>',
                        '</div>',
                        '<h3>' + theme.trans('scripts.templates.signup.please_wait_while_we_create_your_account') + '</h3>',
                    ]);
                    if (this.account) {
                        return this.doAddPaymentMethodRequest();
                    } else {
                        return Givecloud.Account.signup(this.signup)
                            .then(function(data) {
                                this.account = data.account;
                                return this.doAddPaymentMethodRequest();
                            }.bind(this)).catch(function(data) {
                                theme.ladda.stop(this.$refs.submitBtn);
                                this.error = theme.error(data);
                                this.$nextTick(function() {
                                    theme.scrollIntoView(this.$refs.error, 50);
                                });
                            }.bind(this));
                    }
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
                    return Givecloud.Account.PaymentMethods.create(this.payment_method)
                        .then(function(payment_method) {
                            payment_method.payment_type = this.signup.payment_type;
                            this.payment.name = this.payment.name || payment_method.billing_address.name;
                            this.payment.address_line1 = payment_method.billing_address.address1;
                            this.payment.address_line2 = payment_method.billing_address.address2;
                            this.payment.address_city = payment_method.billing_address.city;
                            this.payment.address_state = payment_method.billing_address.province_code;
                            this.payment.address_zip = payment_method.billing_address.zip;
                            this.payment.address_country = payment_method.billing_address.country_code;
                            return Givecloud.Account.PaymentMethods.tokenize(payment_method, this.payment, payment_method.payment_type);
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
            }
        });
    }

    if (document.querySelectorAll('#signup-app').length) {
        return new SignupApp('#signup-app');
    }

})(theme);
