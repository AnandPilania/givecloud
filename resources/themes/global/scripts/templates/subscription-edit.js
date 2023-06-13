theme.subscriptionsEdit = function (t) {

    function SubscriptionsEdit(selector) {
        this.vm = new Vue({
            el: selector,
            delimiters: ['${', '}'],
            data: {
                subscription: {},
                paymentMethods: [],
                currencyCode: null,
                canCoverCosts: Givecloud.config.processing_fees.cover,
                input: {
                    id: null,
                    amount: 0,
                    billing_period: null,
                    next_payment_date: null,
                    payment_method_id: null,
                    cover_fees: false,
                    cover_costs_type: Givecloud.config.processing_fees.using_ai ? 'more_costs' : null,
                },
                permissions: {
                    can_edit_subscription_amount: false,
                    can_edit_subscription_frequency: false,
                    can_edit_subscription_date: false
                },
                submitBtn: null,
                billing_periods: [
                    ['Day', theme.trans('scripts.templates.subscription_edit.daily')],
                    ['Week', theme.trans('scripts.templates.subscription_edit.weekly')],
                    ['SemiMonth', theme.trans('scripts.templates.subscription_edit.semi_monthly')],
                    ['Month', theme.trans('scripts.templates.subscription_edit.monthly')],
                    ['Quarter', theme.trans('scripts.templates.subscription_edit.quarterly')],
                    ['SemiYear', theme.trans('scripts.templates.subscription_edit.semi_yearly')],
                    ['Year', theme.trans('scripts.templates.subscription_edit.yearly')],
                ],
                fees: {
                    amount: Givecloud.config.processing_fees.amount,
                    rate: Givecloud.config.processing_fees.rate,
                },
                $calendar: null
            },
            mounted: function () {
                this.checkPermissions(JSON.parse(this.$el.attributes['data-site-account-features'].value));
                this.canCoverCosts = this.$el.attributes['data-can-cover-costs'].value ? true : false;
                this.subscription = JSON.parse(this.$el.attributes['data-subscription'].value);
                this.paymentMethods = JSON.parse(this.$el.attributes['data-payment-methods'].value);
                this.currencyCode = this.subscription.currency.iso_code;
                this.input.id = this.subscription.id;
                this.input.amount = this.subscription.amount;
                this.input.billing_period = this.subscription.billing_period;
                this.input.next_payment_date = this.subscription.next_payment_date;
                this.input.payment_method_id = this.subscription.payment_method.id;
                this.input.cover_fees = this.subscription.cover_costs_enabled;
                this.input.cover_costs_type = this.subscription.cover_costs_type || '';
                if(this.subscription.has_legacy_cover_costs) {
                    this.input.cover_costs_type = 'original';
                }
                // If cover costs were already enabled, ensure we keep the existing fees
                if (this.subscription.cover_costs_enabled) {
                    this.fees.amount = this.subscription.cover_costs_cost_per_order;
                    this.fees.rate = this.subscription.cover_costs_percentage;
                }
                this.submitBtn = Ladda.create(this.$refs.submitBtn);
                this.$calendar = $(this.$refs.paymentDate);
                this.setupDateSelector();
                this.buildDateSelector();
            },
            computed: {
                cover_costs_amounts: function() {
                    return Givecloud.Dcc.getCosts(parseFloat(this.input.amount));
                },
                total_fees: function () {
                    return this.input.amount > 0 ? Givecloud.Dcc.getCost(this.input.amount, this.input.cover_costs_type) : 0;
                },
                billing_period_name: function () {
                    var name = this.input.billing_period; // fallback
                    for (var i = 0; i < this.billing_periods.length; i++) {
                        if (this.billing_periods[i][0] === this.input.billing_period) {
                            name = this.billing_periods[i][1];
                        }
                    }
                    return name;
                }
            },
            watch: {
                'input.billing_period': function () {
                    this.buildDateSelector();
                },
                'permissions.can_edit_subscription_date': function (newValue) {
                    if (newValue) {
                        Vue.nextTick(function () {
                            this.$calendar = $(this.$refs.paymentDate);
                            this.setupDateSelector();
                            this.buildDateSelector();
                        }, this)
                    } else {
                        this.destroyDateSelector();
                    }
                },
                'permissions.can_edit_subscription_frequency': function () {
                    this.buildDateSelector();
                }
            },
            methods: {
                checkPermissions: function (permissions) {
                    this.permissions.all = permissions;
                    this.permissions.can_edit_subscription_amount = this.hasPermission('edit-subscription-amount');
                    this.permissions.can_edit_subscription_frequency = this.hasPermission('edit-subscription-frequency');
                    this.permissions.can_edit_subscription_date = this.hasPermission('edit-subscription-date');
                },
                hasPermission: function (permission) {
                    return this.permissions.all.indexOf(permission) === -1 ? false : true;
                },
                validateForm: function () {
                    return theme.bsFormValidate(this.$el);
                },
                submit: function () {
                    if (this.validateForm()) {
                        this.submitBtn.start();
                        Givecloud.Account.Subscriptions.update(this.input.id, this.input)
                            .then(function(account) {
                                theme.toast.success(theme.trans('scripts.templates.subscription_edit.recurring_payment_saved'));
                                location = '/account/subscriptions/' + account.id;
                            })
                            .catch(function(err) {
                                theme.toast.error(err);
                            }).finally(function() {
                                this.submitBtn.stop();
                            }.bind(this));
                    }
                },
                getDateSelectorOptions: function () {
                    var allowed_days = this.$calendar.data('payment-day-options'),
                        allowed_weekdays = [],
                        opts = {};

                    if (this.$calendar.data('payment-weekday-options')) {
                        for (var d in this.$calendar.data('payment-weekday-options')) {
                            allowed_weekdays.push(d);
                        }
                    }

                    if (this.input.billing_period === 'Day' || this.input.billing_period === 'Week' || this.input.billing_period === 'SemiMonth') {
                        opts.beforeShowDay = function(date) {
                            var is_allowed = (allowed_weekdays.indexOf(""+date.getDay()) >= 0);
                            return {
                                'enabled' : is_allowed,
                                'classes' : null,
                                'tooltip' : null,
                                'content' : null
                            };
                        }
                    } else {
                        opts.beforeShowDay = function(date) {
                            var is_allowed = (allowed_days.indexOf(""+date.getDate()) >= 0);
                            return {
                                'enabled' : is_allowed,
                                'classes' : null,
                                'tooltip' : null,
                                'content' : null
                            };
                        }
                    }
                    return opts;
                },
                setupDateSelector: function () {
                    var $this = this;
                    this.$calendar.on('changeDate', function() {
                        console.log($this.$calendar.datepicker('getFormattedDate'));
                        console.log($this.input.next_payment_date);
                        $this.input.next_payment_date = $this.$calendar.datepicker('getFormattedDate');
                        console.log($this.input.next_payment_date);
                    });
                },
                destroyDateSelector: function () {
                    this.$calendar.datepicker('destroy');
                },
                buildDateSelector: function () {
                    this.destroyDateSelector();
                    this.$calendar.datepicker(this.getDateSelectorOptions());
                }
            },
            filters: {
                money: function (value, currencyCode) {
                    return theme.money(value, currencyCode);
                },
                ordinal: function (value) {
                    value = parseInt(value);
                    if (value) {
                        return Sugar.Number.ordinalize(value);
                    }
                },
                size: function (value) {
                    return Sugar.Object.size(value);
                },
                date: function (dateString) {
                    if (! dateString) {
                        return '';
                    }

                    return new Intl
                        .DateTimeFormat(undefined, { dateStyle: "long" })
                        .format(new Date(dateString + ' 00:00:00'))
                }
            }
        });
    }

    if (document.querySelectorAll('#subscription-edit').length) {
        return new SubscriptionsEdit('#subscription-edit');
    }

};


$(document).ready(function(){
    theme.subscriptionsEdit(theme);
});
