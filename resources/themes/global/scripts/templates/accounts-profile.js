theme.profile = (function(){

    function data_get(data, property) {
        return Sugar.Object.get(data, property);
    }

    function ProfileComponent(selector) {
        this.selector = selector;

        function updateInput(account) {
            return {
                title:                   data_get(account, 'title'),
                first_name:              data_get(account, 'first_name'),
                last_name:               data_get(account, 'last_name'),
                organization_name:       data_get(account, 'organization_name'),
                email:                   data_get(account, 'email'),
                last_optin_log:          data_get(account, 'last_optin_log'),
                billing_title:           data_get(account, 'billing_address.title'),
                billing_first_name:      data_get(account, 'billing_address.first_name'),
                billing_last_name:       data_get(account, 'billing_address.last_name'),
                billing_email:           data_get(account, 'billing_address.email'),
                billing_address_01:      data_get(account, 'billing_address.address1'),
                billing_address_02:      data_get(account, 'billing_address.address2'),
                billing_company:         data_get(account, 'billing_address.company'),
                billing_city:            data_get(account, 'billing_address.city'),
                billing_province_code:   data_get(account, 'billing_address.province_code'),
                billing_zip:             data_get(account, 'billing_address.zip'),
                billing_country_code:    data_get(account, 'billing_address.country_code'),
                billing_phone:           data_get(account, 'billing_address.phone'),
                shipping_title:          data_get(account, 'shipping_address.title'),
                shipping_first_name:     data_get(account, 'shipping_address.first_name'),
                shipping_last_name:      data_get(account, 'shipping_address.last_name'),
                shipping_email:          data_get(account, 'shipping_address.email'),
                shipping_address_01:     data_get(account, 'shipping_address.address1'),
                shipping_address_02:     data_get(account, 'shipping_address.address2'),
                shipping_company:        data_get(account, 'shipping_address.company'),
                shipping_city:           data_get(account, 'shipping_address.city'),
                shipping_province_code:  data_get(account, 'shipping_address.province_code'),
                shipping_zip:            data_get(account, 'shipping_address.zip'),
                shipping_country_code:   data_get(account, 'shipping_address.country_code'),
                shipping_phone:          data_get(account, 'shipping_address.phone')
            };
        }

        this.vm = new Vue({
            el: this.selector,
            delimiters: ['${', '}'],
            data: {
                account: null,
                billing_province_label: theme.trans('general.forms.state'),
                shipping_province_label: theme.trans('general.forms.state'),
                input: Object.assign(updateInput(), {
                    email_opt_in: false,
                    email_opt_out_reason: null,
                }),
                credentials: {
                    password: null,
                    password_confirmation: null,
                },
                changePassword: false,
                formValidated: false,
                overlayTimer: null,
                was_opted_in: false,
            },
            mounted: function () {
                this.account = JSON.parse(this.$el.attributes['account'].value);
                this.was_opted_in = this.account.last_optin_log && this.account.last_optin_log.action === 'optin';
            },
            watch: {
                account: function(account) {
                    var lastOptinLog = account.last_optin_log || false;

                    this.input = Object.assign(updateInput(account), {
                        email_opt_in: lastOptinLog && lastOptinLog.action === 'optin',
                        email_opt_out_reason: lastOptinLog && lastOptinLog.action  === 'optout' ? lastOptinLog.reason : null,
                    });
                },
                'input.billing_country_code': function(new_value, old_value) {
                    if (new_value !== old_value) {
                        //this.input.billing_province_code = null;
                    }
                },
                'input.email_opt_out_reason': function(new_value, old_value) {
                    if (new_value !== old_value && new_value === 'other') {
                        this.$nextTick(function () {
                            this.$refs.email_opt_out_reason_other.focus();
                        });
                    }
                }
            },
            computed: {
                donor_title_options: function() {
                    return Givecloud.config.title_options;
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
                        return this.getCountryStates(this.input.billing_country_code, 'billing_province_label');
                    },
                    default: {},
                },
                shipping_subdivisions: {
                    get: function () {
                        return this.getCountryStates(this.input.shipping_country_code, 'shipping_province_label');
                    },
                    default: {},
                }
            },
            methods: {
                fillAddressFields: function (address) {
                    this.input.billing_address_01 = address.line1;
                    this.input.billing_address_02 = address.line2;
                    this.input.billing_city = address.city;
                    this.input.billing_zip = address.zip;
                    this.input.billing_country_code = address.country_code;

                    // Deal with province/state dropdown differently as it is populated by an asyncComputed property.
                    // I extracted it to a function to reuse it here, wrapped in a promise
                    // to be able to select the correct province / state.
                    this.getCountryStates(this.input.billing_country_code, 'billing_province_label').then(function (states) {
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
                getCountryStates: function (country, label) {
                    label = label || 'billing_province_label';
                    return Givecloud.Services.Locale.subdivisions(country)
                        .then(function(data) {
                            if (Sugar.Object.size(data.subdivisions)) {
                                this[label] = theme.trans('general.subdivision_types.' + Sugar.String.underscore(data.subdivision_type));
                                return Promise.resolve(data.subdivisions);
                            }

                            return Promise.resolve(null);
                        }.bind(this));
                },
                editCredentials: function() {
                    this.changePassword = true;
                    this.$nextTick(theme.arrow(this, function() {
                        this.$refs.password.focus();
                    }));
                },
                cancelCredentials: function() {
                    this.changePassword = false;
                    this.credentials.password = null;
                    this.credentials.password_confirmation = null;
                },
                submitProfileForm: function() {
                    this.formValidated = true;
                    this.$validator.validateAll().then(function(valid) {
                        if (valid) {
                            var data = this.input;
                            if (this.credentials.password || this.credentials.password_confirmation) {
                                data = Sugar.Object.add(data, this.credentials);
                            }

                            var btn = Ladda.create(document.querySelector('button[type=submit]'));
                            btn.start();

                            Givecloud.Account.update(data)
                                .then(function() {
                                    theme.toast.success('Profile saved!');
                                    this.was_opted_in = data.email_opt_in;
                                    this.input.email_opt_out_reason = null;
                                }.bind(this))
                                .catch(function(err) {
                                    theme.toast.error(err);
                                }).finally(function() {
                                    btn.stop();
                                });
                        }
                    }.bind(this));
                },
                copyBillingToShipping:function() {
                    this.input.shipping_title         = this.input.billing_title,
                    this.input.shipping_first_name    = this.input.billing_first_name,
                    this.input.shipping_last_name     = this.input.billing_last_name,
                    this.input.shipping_email         = this.input.billing_email,
                    this.input.shipping_address_01    = this.input.billing_address_01,
                    this.input.shipping_address_02    = this.input.billing_address_02,
                    this.input.shipping_company       = this.input.billing_company,
                    this.input.shipping_city          = this.input.billing_city,
                    this.input.shipping_province_code = this.input.billing_province_code,
                    this.input.shipping_zip           = this.input.billing_zip,
                    this.input.shipping_country_code  = this.input.billing_country_code,
                    this.input.shipping_phone         = this.input.billing_phone
                }
            }
        });
    }


    if (document.querySelectorAll('#profile-app').length) {
        return new ProfileComponent('#profile-app');
    }

})();
