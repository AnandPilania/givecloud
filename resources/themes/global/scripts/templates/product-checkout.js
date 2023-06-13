theme.productCheckout = (function(t){

    function ProductCheckoutComponent(selector) {
        this.vm = new Vue({
            el: selector,
            delimiters: ['${', '}'],
            data: {
                amt: 0,
                pledge_campaign: null,
                billing_period: 'onetime',
                billing_province_label: theme.trans('general.forms.state'),
                recurring_with_initial_charge: false,
                cart: null,
                account: null,
                input: {
                    account_type_id: 1,
                    currency_code: Givecloud.config.currency.code,
                    payment_type: Givecloud.Gateway.getDefaultPaymentType(),
                    billing_title: null,
                    billing_first_name: null,
                    billing_last_name: null,
                    billing_company: null,
                    billing_email: null,
                    email_opt_in: false,
                    billing_address1: null,
                    billing_address2: null,
                    billing_city: null,
                    billing_province_code: null,
                    billing_zip: null,
                    billing_country_code: Givecloud.config.billing_country_code,
                    billing_phone: null,
                    cover_fees: Givecloud.config.processing_fees.cover,
                    cover_costs_type: Givecloud.config.processing_fees.using_ai ? 'more_costs' : null,
                    is_anonymous: false,
                    comments: ''
                },
                payment: {
                    currency: Givecloud.config.currency.code,
                },
                payValidated: false,
                pledgeValidated: false,
                processing: false,
                overlayTimer: null,
                referral_source: null,
                requires_captcha: Givecloud.config.requires_captcha,
                redirect_url: null,
                gift_aid: false,
                gl_code: null,
            },
            created: function() {
                theme.fire('theme.productCheckout:created', [this]);
            },
            mounted: function(){
                var self = this;

                theme.product.init(this.$el);

                $(this.$el).find('input[name=amt]').change(function(event){
                    self.amt = parseFloat(event.target.value, 10) || 0;
                }).change();

                $(this.$el).find('input[name=variant_id]').change(function(){
                    var value = String($(self.$el).find('input[name=variant_id]:checked').val() || '');
                    self.pledge_campaign = value.match(/^pledge_campaign:/)
                        ? theme.toNumber(value.replace(/^pledge_campaign:(.*)$/,'$1'))
                        : null;
                }).change();

                $(this.$el).find('input[name=recurring_frequency]').change(function(event){
                    self.billing_period = event.target.value || 'onetime';
                }).change();

                $(this.$el).find('input[name=recurring_with_initial_charge]').change(function(event){
                    self.recurring_with_initial_charge = (event.target.type === 'hidden')
                        ? event.target.value : event.target.checked;
                }).change();

                if (!this.$refs.cover_fees || $(this.$refs.cover_fees).data('state') === 'unchecked') {
                    this.input.cover_fees = false;
                }

                if (this.$refs.designation_options) {
                    this.gl_code = $(this.$refs.designation_options).data('default-account') || null;
                }

                if (this.$refs.referral_source) {
                    this.input.referral_source = this.$refs.referral_source.value;
                }
                if (this.$refs.redirect_url) {
                    this.redirect_url = this.$refs.redirect_url.value;
                }

                $(document).on('gc-number-type', function(event){
                    self.$set(self.payment, 'number_type', event.detail);
                });

                theme.collectEvent('view');
            },
            watch: {
                referral_source: function(new_value, old_value) {
                    var self = this;
                    if (new_value !== old_value) {
                        if (new_value === 'other') {
                            this.input.referral_source = null;
                            this.$nextTick(function(){
                                self.$refs.referral_source_other.focus();
                            });
                        } else {
                            this.input.referral_source = new_value;
                        }
                    }
                },
                'input.currency_code': function(new_value, old_value) {
                    if (new_value !== old_value) {
                        this.payment.currency = new_value;
                        if (this.input.payment_type === 'bank_account' && !this.supportedPaymentType('bank_account')) {
                            this.input.payment_type = 'credit_card'
                        }
                        jQuery(this.$refs.productOptionForm).find('input[name=variant_id]').first().change();
                        jQuery('body').removeClass(function (index, css) {
                            return (css.match(/\bcurrency-\S+/g) || []).join(' ');
                        });
                        jQuery('body').addClass(('currency-' + new_value).toLowerCase());
                    }
                },
                'input.cover_costs_type': function(new_value, old_value) {
                    if (new_value !== old_value) {
                        this.input.cover_fees = !!new_value;
                    }
                },
                'input.payment_type': function(new_value) {
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
                account_type: function() {
                    return this.account_types.find(function(type) {
                        return type.id == this.input.account_type_id;
                    }.bind(this));
                },
                account_types: function() {
                    return Givecloud.config.account_types;
                },
                config: function() {
                    return Givecloud.config;
                },
                cover_costs_amounts: function() {
                    return Givecloud.Dcc.getCosts(this.amt);
                },
                currency: function() {
                    var matchingCurrency = this.currencies.find(t.arrow(this, function(currency) {
                        return currency.code === this.input.currency_code;
                    }));
                    return {
                        code: this.input.currency_code,
                        symbol: matchingCurrency ? matchingCurrency.symbol : Givecloud.config.currency.symbol,
                    };
                },
                currencies: function() {
                    return Givecloud.config.currencies;
                },
                donor_title_options: function() {
                    return Givecloud.config.title_options;
                },
                fees: function() {
                    return this.amt > 0 ? Givecloud.Dcc.getCost(this.amt, this.input.cover_costs_type) : 0;
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
                    if (gateway && (this.is_onetime || gateway.referenceTransactions)) {
                        return true;
                    }
                },
                paysafe_checkout_available: function() {
                    var gateway = Givecloud.PaymentTypeGateway('credit_card');
                    if (Givecloud.config.gateways.credit_card === 'paysafe' && gateway.useCheckout) {
                        if (this.is_onetime || this.recurring_with_initial_charge) {
                            return this.total_amt > 0;
                        }
                    }
                },
                is_onetime: function() {
                    return this.billing_period === 'onetime';
                },
                referral_sources: function() {
                    return Givecloud.config.referral_sources;
                },
                total_amt: function() {
                    if (this.input.cover_fees) {
                        return Sugar.Number.round(this.amt + this.fees, 2);
                    }
                    return this.amt;
                },
                billing_province_required: function() {
                    if (this.billing_subdivisions && Object.keys(this.billing_subdivisions).length) return 'required';
                },
                unsupported_card_type: function() {
                    return this.payment.number_type && !this.supportedCardType(this.payment.number_type);
                },
                is_uk_address: function () {
                    return this.input.billing_country_code === 'GB';
                },
            },
            asyncComputed: {
                countries: {
                    get: function() {
                        return Givecloud.Services.Locale.countries().then(function(data) {
                            var countries = [];
                            if (Givecloud.config.force_country) {
                                countries.push({
                                    value: Givecloud.config.force_country,
                                    label: data.countries[Givecloud.config.force_country] || Givecloud.config.force_country,
                                });
                            } else {
                                Givecloud.config.pinned_countries.forEach(function (code) {
                                    countries.push({
                                        value: code,
                                        label: data.countries[code] || code,
                                    });
                                    if (data.countries[code]) {
                                        delete data.countries[code];
                                    }
                                });
                                if (countries.length) {
                                    countries.push({ value: '', label: '--------' });
                                }
                                Sugar.Object.forEach(data.countries, function(name, code) {
                                    countries.push({ value: code, label: name });
                                });
                            }
                            return Promise.resolve(countries);
                        });
                    },
                    default: {}
                },
                billing_subdivisions: {
                    get: function () {
                        return this.getCountryStates(this.input.billing_country_code);
                    },
                    default: {},
                },
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
                money: function(value, includeCode) {
                    return theme.money(value, this.input.currency_code, {
                        showCurrencyCode: includeCode && Givecloud.config.money_with_currency
                    });
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
                paymentOverlay: function(status) {
                    var $modal = $('#payment-overlay'),
                        modal = $modal.modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: false
                    }).data('bs.modal');
                    if (status === 'hide') {
                        if (this.overlayTimer) {
                            clearTimeout(this.overlayTimer);
                            this.overlayTimer = null;
                        } else {
                            // bootstrap won't hide the modal if it's transitioning
                            modal._isTransitioning = false;
                            modal.hide();
                        }
                    } else {
                        $modal.find('.spinner').addClass('d-none');

                        if (status === 'success') {
                            $modal.find('.spinner-success').removeClass('d-none');
                        } else {
                            $modal.find('.spinner-spin').removeClass('d-none');
                        }

                        // avoid displaying modal for quick transactions
                        this.overlayTimer = setTimeout(t.arrow(this, function() {
                            this.overlayTimer = null;
                            modal.show();
                        }), 250);
                    }
                },
                scrollToError: function() {
                    var element = jQuery(this.$el).find('.has-errors,input:invalid').filter(':visible').first();
                    t.scrollIntoView(element, 85).then(function(element){
                        element.find(':input').focus();
                    });
                },
                validatePayForm: function(showErrors) {
                    if (showErrors === void 0 || showErrors) {
                        this.payValidated = true;
                    }
                    if (t.bsFormValidate(this.$refs.form) === false) {
                        this.scrollToError();
                        this.$validator.validateAll('pay');
                        return Promise.reject();
                    }
                    return this.$validator.validateAll('pay')
                        .then(function(valid) {
                            if (this.payment.number_type && !this.supportedCardType(this.payment.number_type)) {
                                return Promise.reject('Unsupport card type. Please try again with a different card.');
                            }
                            if (!valid) {
                                this.scrollToError();
                                return Promise.reject();
                            } else {
                                return Promise.resolve();
                            }
                        }.bind(this));
                },
                submitPayForm: function() {
                    if (this.pledge_campaign) {
                        return this.submitPledgeForm();
                    }
                    if (this.processing) {
                        return Promise.reject('processing lock in place');
                    }
                    this.retainProcessingLock();
                    theme.collectEvent('click_pay');
                    return this.validatePayForm()
                        .then(function() {
                            this.input.item = {form_fields: {}};
                            this.input.cover_costs_enabled = this.input.cover_fees;

                            $([this.$refs.productOptionForm, this.$refs.productForm]).find(':input')
                                .serializeArray()
                                .forEach(t.arrow(this, function(field){
                                    t.data_set(this.input.item, field.name, field.value);
                                }));

                            this.input.item.amt = this.amt;
                            this.input.item.gift_aid = this.gift_aid;
                            this.input.item.gl_code = this.gl_code;

                            this.paymentOverlay();
                            theme.ladda.start('#btn-pay');

                            Givecloud.Cart.oneClickCheckout(this.input, this.cart, this.input.payment_type)
                                .then(function(data){
                                    this.cart = data.cart;
                                    this.account = this.cart.account;
                                    if (this.cart.requires_payment) {
                                        this.payment.name = this.cart.billing_address.name;
                                        this.payment.address_line1 = this.cart.billing_address.address1;
                                        this.payment.address_line2 = this.cart.billing_address.address2;
                                        this.payment.address_city = this.cart.billing_address.city;
                                        this.payment.address_state = this.cart.billing_address.province_code;
                                        this.payment.address_zip = this.cart.billing_address.zip;
                                        this.payment.address_country = this.cart.billing_address.country_code;
                                        return this.gateway.getCaptureToken(this.cart, this.payment, this.input.payment_type, this.input.recaptcha_response, this.payment.save_payment_method)
                                            .then(function(token) {
                                                return this.gateway.chargeCaptureToken(this.cart, token);
                                            }.bind(this));
                                    }
                                    return Givecloud.Cart(this.cart.id).complete();
                                }.bind(this)).then(function() {
                                    this.releaseProcessingLock();
                                    this.paymentOverlay('success');
                                    theme.collectContributionPaid(this.cart);
                                    setTimeout(t.arrow(this, function() {
                                        if (this.redirect_url) {
                                            window.location.href = theme.applyCartUrlSubstitutions(this.cart, this.redirect_url);
                                        } else {
                                            window.location.href = '/contributions/' + this.cart.id + '/thank-you';
                                        }
                                    }),1000);
                                }.bind(this)).catch(function(err) {
                                    if (err) {
                                        if (theme.data_get(err, 'data.cart')) {
                                            this.cart = theme.data_get(err, 'data.cart');
                                        }
                                        if (theme.data_get(err, 'data.captcha')) {
                                            if (this.requires_captcha) {
                                                this.$refs.recaptcha.reset();
                                            } else {
                                                this.requires_captcha = true;
                                            }
                                        }
                                        theme.toast.error(err);
                                    }
                                    this.releaseProcessingLock();
                                    this.paymentOverlay('hide');
                                    theme.ladda.stop('#btn-pay');
                                }.bind(this));
                        }.bind(this)).catch(function(err) {
                            this.releaseProcessingLock();
                            if (err) {
                                theme.toast.error(err);
                            }
                        }.bind(this));
                },
                submitPledgeForm: function() {
                    if (this.processing) {
                        return Promise.reject('processing lock in place');
                    }
                    this.retainProcessingLock();
                    this.pledgeValidated = true;
                    return this.$validator.validateAll('pledge')
                        .then(function(valid) {
                            if (valid) {
                                this.paymentOverlay();
                                theme.ladda.start('#btn-pledge');

                                return Givecloud.PledgeCampaigns.createPledge(this.pledge_campaign, {
                                    amount: this.amt,
                                    currency_code: this.input.currency_code,
                                    first_name: this.input.billing_first_name,
                                    last_name: this.input.billing_last_name,
                                    email: this.input.billing_email,
                                    phone: this.input.billing_phone,
                                    is_anonymous: this.input.is_anonymous,
                                    comments: this.input.comments,
                                    'g-recaptcha-response': this.input.recaptcha_response
                                });
                            }
                            this.releaseProcessingLock();
                            this.scrollToError();
                            return Promise.reject();
                        }.bind(this)).then(function(pledge) {
                            this.releaseProcessingLock();
                            this.paymentOverlay('success');
                            setTimeout(function() {
                                if (this.redirect_url) {
                                    window.location.href = this.redirect_url;
                                } else {
                                    window.location.href = '/pledge/' + pledge.pledge_number + '/thank-you';
                                }
                            }.bind(this), 1000);
                            theme.ladda.stop('#btn-pledge');
                        }.bind(this)).catch(function(err) {
                            if (err) {
                                theme.toast.error(err);
                            }
                            this.releaseProcessingLock();
                            this.paymentOverlay('hide');
                            theme.ladda.stop('#btn-pledge');
                        }.bind(this));
                },
                retainProcessingLock: function() {
                    this.processing = true;
                },
                releaseProcessingLock: function() {
                    this.processing = false;
                },
            },
            filters: {
                money: function (value) {
                    return theme.money(value);
                },
                ordinal: function(value) {
                    value = parseInt(value);
                    if (value) {
                        return Sugar.Number.ordinalize(value);
                    }
                },
                size: function(value) {
                    return Sugar.Object.size(value);
                }
            }
        });

        if (Givecloud.config.account_id) {
            Givecloud.Account.get().then(t.arrow(this.vm, function(data) {
                // this needs to happen in the next tick to avoid validation issues
                // https://github.com/baianat/vee-validate/issues/2109
                this.$nextTick(t.arrow(this, function() {
                    this.account = data.account;
                    if (data.account.payment_methods.length) {
                        this.input.payment_type = 'payment_method'
                    }

                    this.input.billing_province_code = t.data_get(data.account, 'billing_address.province_code');
                }));

                this.input.billing_title         = t.data_get(data.account, 'billing_address.title');
                this.input.billing_first_name    = t.data_get(data.account, 'billing_address.first_name', t.data_get(data.account, 'first_name'));
                this.input.billing_last_name     = t.data_get(data.account, 'billing_address.last_name', t.data_get(data.account, 'last_name'));
                this.input.billing_email         = t.data_get(data.account, 'billing_address.email', t.data_get(data.account, 'email'));
                this.input.billing_address1      = t.data_get(data.account, 'billing_address.address1');
                this.input.billing_address2      = t.data_get(data.account, 'billing_address.address2');
                this.input.billing_company       = t.data_get(data.account, 'billing_address.company');
                this.input.billing_city          = t.data_get(data.account, 'billing_address.city');
                this.input.billing_country_code  = t.data_get(data.account, 'billing_address.country_code', Givecloud.config.billing_country_code);
                this.input.billing_zip           = t.data_get(data.account, 'billing_address.zip');
                this.input.billing_phone         = t.data_get(data.account, 'billing_address.phone');
            })).catch(t.noop);
        }
    }

    if (document.querySelectorAll('#product-payment-app').length) {
        return new ProductCheckoutComponent('#product-payment-app');
    }

})(theme);
