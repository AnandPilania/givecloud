/* eslint no-unused-vars: "off" */

var token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token.content }});

    $.ajaxPrefilter(function(options, originalOptions, xhr) {
        if (options.crossDomain) {
            delete options.headers['X-CSRF-TOKEN'];
        }
    });
}

Vue.use(AsyncComputed);
Vue.use(VueTheMask);

Vue.use(VeeValidate, {
    classes: true,
    strict: false,
    validity: true
});

Vue.component('paypal-button', {
    props: {
        validator: {
            type: Function,
            required: false
        }
    },
    template: '<div id="paypal-checkout" style="margin:auto;max-width:350px" />',
    mounted: function() {
        var self = this;
        function renderButton() {
            if (window.paypal) {
                clearInterval(self.paypalCheckoutReady);
                var gateway = Givecloud.config.gateways.paypal;
                var style = {
                    type: 'checkout',
                    size: 'medium',
                    shape: 'pill',
                    color: 'gold'
                };
                if (gateway === 'paypalcheckout') {
                    style = {
                        label: 'pay',
                        size: 'responsive',
                        shape: 'pill',
                        color: 'gold',
                        tagline: false
                    };
                }
                Givecloud.Gateway(gateway).renderButton({
                    id: 'paypal-checkout',
                    style: style,
                    validateForm: self.validator,
                    onPayment: function() {
                        self.$emit('click');
                    }
                });
            }
        }

        // delay rendering to allow time for PayPal to load
        this.paypalCheckoutReady = setInterval(renderButton.bind(this), 10);
    },
    beforeDestroy: function() {
        if (this.paypalCheckoutReady) {
            clearInterval(this.paypalCheckoutReady);
        }
    }
});

Vue.component('credit-card', {
    template: '<div class="credit-card-fields"><slot></slot></div>',
    computed: {
        gateway: function() {
            return Givecloud.PaymentTypeGateway('credit_card');
        }
    },
    mounted: function() {
        this.$nextTick(this.setupFields.bind(this));
    },
    methods: {
        setupFields: function() {
            switch (this.gateway.$name) {
                case 'braintree': return this.setupPaysafeFields();
                case 'paysafe': return this.setupPaysafeFields();
                case 'stripe': return this.setupStripeFields();
            }
        },
        setupPaysafeFields: function() {
            if (! document.getElementById('inputPaymentNumber')) {
                return;
            }
            this.gateway.setupFields({
                cardNumber: {
                    selector: '#inputPaymentNumber',
                    placeholder: '0000 0000 0000 0000',
                    separator: ' '
                },
                expiryDate: {
                    selector: '#inputPaymentExpiry',
                    placeholder: theme.trans('general.forms.expiry_placeholder')
                },
                cvv: {
                    selector: '#inputPaymentCVV',
                    placeholder: '000',
                    optional: false
                }
            });
        },
        setupStripeFields: function() {
            if (! document.getElementById('inputPaymentNumber')) {
                return;
            }
            this.gateway.setupFields({
                cardNumber: {
                    selector: '#inputPaymentNumber',
                    placeholder: '0000 0000 0000 0000',
                    container: '.labelify'
                },
                cardExpiry: {
                    selector: '#inputPaymentExpiry',
                    placeholder: 'MM / YY',
                    container: '.labelify'
                },
                cardCvc: {
                    selector: '#inputPaymentCVV',
                    placeholder: '000',
                    container: '.labelify'
                }
            });
        }
    },
});

Vue.component('payment-field', {
    props: {
        field: {
            type: String,
            required: true
        }
    },
    computed: {
        gateway: function() {
            return Givecloud.PaymentTypeGateway('credit_card');
        },
        usesHostedPaymentFields: function() {
            return this.gateway && this.gateway.usesHostedPaymentFields();
        }
    },
    template: '<div data-private><div v-if="usesHostedPaymentFields" :id="field" class="form-control gateway-form-control"></div><slot v-else></slot></div>',
});

Vue.component('wallet-pay', {
    template: [
        '<div class="wallet-pay-fields">',
            '<button v-if="useApplePayButton" type="button" class="apple-pay-button" @click="attemptWalletPay(\'applePay\')">',
                '<div class="apple-pay-official-button" lang="en"></div>',
                '<div class="apple-pay-unofficial-button">',
                    '<i class="fa fa-apple" aria-hidden="true"></i> &nbsp; Pay',
                '</div>',
            '</button>',
            '<div v-else-if="useGooglePayButton" class="google-pay-button google-pay-unofficial-button">',
                '<div ref="googlePayButtonRef"></div>',
            '</div>',
            '<button v-else-if="useUnofficialGooglePayButton" type="button" class="google-pay-button" @click="attemptWalletPay(\'googlePay\')">',
                '<span>' + Sugar.Object.get(window.themeLocalizationMap, 'scripts.components.donate_with') + '</span> &nbsp;&nbsp; <svg width="41" height="17" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M19.526 2.635v4.083h2.518c.6 0 1.096-.202 1.488-.605.403-.402.605-.882.605-1.437 0-.544-.202-1.018-.605-1.422-.392-.413-.888-.62-1.488-.62h-2.518zm0 5.52v4.736h-1.504V1.198h3.99c1.013 0 1.873.337 2.582 1.012.72.675 1.08 1.497 1.08 2.466 0 .991-.36 1.819-1.08 2.482-.697.665-1.559.996-2.583.996h-2.485v.001zm7.668 2.287c0 .392.166.718.499.98.332.26.722.391 1.168.391.633 0 1.196-.234 1.692-.701.497-.469.744-1.019.744-1.65-.469-.37-1.123-.555-1.962-.555-.61 0-1.12.148-1.528.442-.409.294-.613.657-.613 1.093m1.946-5.815c1.112 0 1.989.297 2.633.89.642.594.964 1.408.964 2.442v4.932h-1.439v-1.11h-.065c-.622.914-1.45 1.372-2.486 1.372-.882 0-1.621-.262-2.215-.784-.594-.523-.891-1.176-.891-1.96 0-.828.313-1.486.94-1.976s1.463-.735 2.51-.735c.892 0 1.629.163 2.206.49v-.344c0-.522-.207-.966-.621-1.33a2.132 2.132 0 0 0-1.455-.547c-.84 0-1.504.353-1.995 1.062l-1.324-.834c.73-1.045 1.81-1.568 3.238-1.568m11.853.262l-5.02 11.53H34.42l1.864-4.034-3.302-7.496h1.635l2.387 5.749h.032l2.322-5.75z" fill="#FFF" /><path d="M13.448 7.134c0-.473-.04-.93-.116-1.366H6.988v2.588h3.634a3.11 3.11 0 0 1-1.344 2.042v1.68h2.169c1.27-1.17 2.001-2.9 2.001-4.944" fill="#4285F4" /><path d="M6.988 13.7c1.816 0 3.344-.595 4.459-1.621l-2.169-1.681c-.603.406-1.38.643-2.29.643-1.754 0-3.244-1.182-3.776-2.774H.978v1.731a6.728 6.728 0 0 0 6.01 3.703" fill="#34A853" /><path d="M3.212 8.267a4.034 4.034 0 0 1 0-2.572V3.964H.978A6.678 6.678 0 0 0 .261 6.98c0 1.085.26 2.11.717 3.017l2.234-1.731z" fill="#FABB05" /><path d="M6.988 2.921c.992 0 1.88.34 2.58 1.008v.001l1.92-1.918C10.324.928 8.804.262 6.989.262a6.728 6.728 0 0 0-6.01 3.702l2.234 1.731c.532-1.592 2.022-2.774 3.776-2.774" fill="#E94235" /></g></svg>',
            '</button>',
        '</div>',
    ].join(''),
    data: function() {
        return {
            canMakePayment: null,
        };
    },
    computed: {
        gateway: function() {
            return Givecloud.PaymentTypeGateway('wallet_pay');
        },
        useApplePayButton: function() {
            return !!(this.canMakePayment && this.canMakePayment.applePay);
        },
        useGooglePayButton: function() {
            return !!(this.canMakePayment && this.canMakePayment.googlePay && this.gateway.createGooglePayButton);
        },
        useUnofficialGooglePayButton: function() {
            return !!(this.canMakePayment && this.canMakePayment.googlePay && !this.gateway.createGooglePayButton);
        }
    },
    mounted: function() {
        this.$nextTick(this.setupFields.bind(this));
    },
    methods: {
        setupFields: function() {
            this.gateway.canMakePayment().then(function(canMakePayment) {
                this.canMakePayment = canMakePayment;
            }.bind(this));
        },
        attemptWalletPay: function(walletType) {
            this.gateway.getWalletPayToken(
                this.$parent.cart.total_price,
                this.$parent.cart.currency.code,
                walletType
            ).then(function(walletPay) {
                this.$parent.input.payment_type = 'wallet_pay';
                this.$parent.input.wallet_type = walletType;
                this.$parent.payment.wallet_pay = walletPay;
                this.$nextTick(function() {
                    this.$emit('click');
                }.bind(this));
            }.bind(this));
        }
    }
});

Vue.component('modal', {
    props: {
        show: {
            type: Boolean,
            required: true,
            default: false
        },
        id: {
            type: String,
            required: true
        },
        delay: {
            type: Number,
            required: false,
            default: 0
        },
        label: {
            type: String,
            required: true
        },
        preventPassiveClose: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    data: function () {
        return {
            overlayTimer: null,
            modal: null
        };
    },
    watch: {
        show: function (newVal, oldVal) { // watch it
            if (oldVal !== newVal) {
                if (newVal === true) {
                    this.overlayTimer = setTimeout(theme.arrow(this, function () {
                        this.overlayTimer = null;
                        this.modal.show();
                    }), this.delay);
                } else {
                    if (this.overlayTimer) {
                        clearTimeout(this.overlayTimer);
                        this.overlayTimer = null;
                    } else {
                        this.modal._isTransitioning = false;
                        this.modal.hide();
                    }
                }
            }
        }
    },
    mounted: function () {
        var $el = $(this.$refs.el);
        this.modal = $el.modal({
            backdrop: (this.preventPassiveClose ? 'static' : true),
            keyboard: (this.preventPassiveClose ? false : true),
            show: this.show
        }).data('bs.modal');
        $el.on('hidden.bs.modal', theme.arrow(this, function() { this.$emit('closed')}));
      },
    template: '<div class="modal fade" ref="el" :id="id" tabindex="-1" role="dialog" :aria-label="label" aria-hidden="true"><div class="modal-dialog" role="document"><slot></slot></div></div>'
});

Vue.component('vue-recaptcha', {
    template: '<div/>',
    props: {
        sitekey: {
            type: String,
            required: true
        }
    },
    mounted: function () {
        var self = this;
        this.$captchaType = Givecloud.config.captcha_type || 'recaptcha';
        if (this.$captchaType === 'hcaptcha') {
            theme.waitFor('hcaptcha.render').then(function () {
                self.$widgetId = hcaptcha.render(self.$el, {
                    sitekey: self.sitekey,
                    callback: self.emitVerify
                });
            });
        } else if (this.$captchaType === 'recaptcha') {
            window.vueCaptchaApiPromise.then(function () {
                self.$widgetId = grecaptcha.render(self.$el, {
                    sitekey: self.sitekey,
                    callback: self.emitVerify
                });
            });
        }
    },
    methods: {
        reset: function() {
            if (this.$captchaType === 'hcaptcha') {
                hcaptcha.reset(this.$widgetId);
            } else if (this.$captchaType === 'recaptcha') {
                grecaptcha.reset(this.$widgetId);
            }
        },
        emitVerify: function(response) {
            this.$emit('verify', response);
        }
    }
});

window.vueCaptchaApiPromise = new Promise(function(resolve, reject) {
    window.vueCaptchaApiLoaded = function(){
        resolve();
    };
});

VeeValidate.Validator.extend('credit_card', Givecloud.CardholderData.validNumber);
VeeValidate.Validator.extend('expiration_date', Givecloud.CardholderData.validExpirationDate);

VeeValidate.Validator.extend('cvv', function(value, args) {
    return Givecloud.CardholderData.validCvv(value, args[0]);
});


window.theme = window.theme || {};

/*================ Sections ================*/
// =require sections/header.js

/*================ Templates ================*/
// =require templates/accounts-payment-methods.js
// =require templates/accounts-login.js


/**
 * Core theme functions.
 */

theme.arrow = function(context, fn) {
    return fn.bind(context);
};

theme.noop = function() {};

theme.waitFor = function(property) {
    return new Promise(function (resolve, reject) {
        (function themeWait() {
            var value = Sugar.Object.get(window, property)
            if (value) return resolve();
            setTimeout(themeWait, 30);
        })();
    });
};

theme.data_get = function(data, property, defaultValue) {
    var value = Sugar.Object.get(data, property);
    if (value === '' || value === null || typeof value === 'undefined') {
        return defaultValue;
    } else {
        return value;
    }
};

theme.data_set = function(data, property, value) {
    Sugar.Object.set(data, property, value);
};

theme.on = function(event, handler) {
    jQuery(document).on(event, handler);
};

theme.fire = function(event, parameters) {
    jQuery(document).trigger(event, parameters);
};

theme.toNumber = function(number, defaultValue) {
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
};

theme.formatNumber = function(number, precision) {
    return Sugar.Number.format(theme.toNumber(number), precision || 2);
};

theme.ladda = {
    create: function(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        if (element) {
            return element.ladda = element.ladda || Ladda.create(element);
        }
    },
    start: function(element) {
        var btn = theme.ladda.create(element);
        if (btn) btn.start();
        return btn;
    },
    stop: function(element) {
        var btn = theme.ladda.create(element);
        if (btn) btn.stop();
        return btn;
    }
};

theme.error = function(text) {
    if (Sugar.Object.isError(text) || Sugar.Object.isObject(text)) {
        console.error(text);
        try {
            text = theme.data_get(text, 'response.data.error')
                || theme.data_get(text, 'response.data.errors')
                || theme.data_get(text, 'response.data.message')
                || theme.data_get(text, 'response.message')
                || theme.data_get(text, 'error.message')
                || theme.data_get(text, 'data.error')
                || theme.data_get(text, 'data.errors')
                || theme.data_get(text, 'data.message')
                || theme.data_get(text, 'message')
                || theme.data_get(text, 'error')
                || text;
        } catch(err) {
            text = theme.trans('scripts.theme.unknown_error_521');
        }
        if (Sugar.Object.isObject(text)) {
            try {
                text = Sugar.Array.flatten(Sugar.Object.values(text))[0];
            } catch(err) {
                text = 'Unknown error (521)';
            }
        }
    }
    return String(text).replace(/[\n\r]/, '<br>');
};

/**
 * Format an amount to local money format.
 *
 * @param   {float}   amount amount of money to format
 * @param   {string}  currency ISO currency code (CAD)
 * @param   {object}  options formatting options
 * @returns {string}  locally formatted amount
 */
theme.money = function (amount, currencyCode, options) {
    currencyCode = currencyCode || Givecloud.config.currency.code;
    options = options || {};
    var moneyOptions = Object.assign({
        locale: options.showCurrencyCode
            ? Givecloud.config.locale.iso
            : theme.getLocaleForCurrency(currencyCode),
        autoFractionDigits: false,
        showCurrencyCode: false,
    }, options);
    var numberFormatOptions = {
        style: 'currency',
        currency: currencyCode,
        currencyDisplay: 'symbol', // "narrowSymbol" is not available on all devices
        maximumFractionDigits: 2
    };
    if (moneyOptions.autoFractionDigits && (amount % 1 === 0 || amount == 0)) {
        numberFormatOptions.minimumFractionDigits = 0;
        numberFormatOptions.maximumFractionDigits = 0;
    }
    var currencySymbol = theme.getSymbolForCurrency(currencyCode);
    var moneyFormatted = new Intl.NumberFormat(moneyOptions.locale, numberFormatOptions).format(amount)
        .replace(currencySymbol, '@@@')
        .replace('NaN', '###')
        .replace(/[A-Z]+/g, '')
        .replace('###', 'NaN')
        .replace('@@@', currencySymbol)
        .trim();
    return moneyOptions.showCurrencyCode
        ? moneyFormatted + ' ' + currencyCode
        : moneyFormatted;
};

theme.getLocaleForCurrency = function(currencyCode) {
    return Givecloud.config.currencies.reduce(function (locale, currency) {
        return currencyCode === currency.code ? currency.locale : locale;
    }, Givecloud.config.locale.iso);
};

theme.getSymbolForCurrency = function(currencyCode) {
    return (0).toLocaleString(theme.getLocaleForCurrency(currencyCode), {
        style: 'currency',
        currency: currencyCode,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).replace(/\d/g, '').trim();
};

theme.getUrlSearchParam = function(name) {
    name = name.replace(/[[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
};

theme.collectEvent = function(eventName, eventValue) {
    if (window.LogRocket) {
        window.LogRocket.track(eventName, typeof eventValue === 'undefined' ? {} : (typeof eventValue === 'object' ? eventValue : { value: eventValue }));
    }
};

theme.collectContributionPaid = function(contribution) {
    theme.collectEvent('contribution_paid', {
        contribution_number: contribution.id,
        amount: contribution.total_price - contribution.cover_costs_amount,
        dcc_amount: contribution.cover_costs_amount,
        dcc_type: contribution.cover_costs_type || 'none',
        revenue: contribution.total_price,
        currency: contribution.currency.code,
        payment_type: contribution.payment_type,
    });
};

theme.toast = (function() {
    var instance = new Toasted({
        theme: 'primary',
        position: 'top-center',
        duration : 6000,
        singleton: true
    });
    function toast(type, text, opts) {
        return instance[type].call(instance, theme.error(text), opts);
    }
    return {
        error:   toast.bind(instance, 'error'),
        info:    toast.bind(instance, 'info'),
        success: toast.bind(instance, 'success'),
        warning: toast.bind(instance, 'warning')
    };
})();

theme.trans = function(key, substitutions) {
    substitutions = substitutions || {};
    var value = Sugar.Object.get(window.themeLocalizationMap, key) || key;
    if (key.endsWith('_count') && typeof value === 'object') {
        var count = Array.isArray(substitutions['count']) ? substitutions['count'].length : 0;
        if (count === 0 && value['zero']) {
            value = value['zero'];
        } else if (count < 2 && value['one']) {
            value = value['one'];
        } else if (count < 3 && value['two']) {
            value = value['two'];
        } else {
            value = value['other'] || key;
        }
    }
    value = String(value).replace(/{{([\s\S]+?)}}/g, function(match, interpolateValue) {
        interpolateValue = Sugar.Object.get(substitutions, interpolateValue.trim());
        return typeof interpolateValue === 'undefined' ? match : interpolateValue;
    });
    if (key.endsWith('_html')) {
        return value;
    }
    return Sugar.String.escapeHTML(value);
};

theme.scrollIntoView = function(element, offset, duration, easing) {
    try {
        element = $(element);
    } catch (err) {
        return Promise.reject(err);
    }
    if (element.length) {
        return new Promise(function(resolve, reject) {
            element.velocity('scroll', {
                offset: offset ? (1 - offset) : 0,
                easing: easing || 'swing',
                duration: duration || 250,
                mobileHA: false,
                complete: function(){
                    resolve(element);
                }
            });
        });
    } else {
        return Promise.reject();
    }
};

theme.cartPreview = function(cart) {
    // slide out a div from the right panel
    // that shows a preview of the cart with
    // a checkout button
};

theme.applyCartUrlSubstitutions = function(cart, url) {
    var substitutions = {
        'order_number': cart.id,
        'billing_first_name': cart.billing_address.first_name,
        'billing_last_name': cart.billing_address.last_name,
        'billing_email': cart.billing_address.email,
        'line_items': cart.line_items.map(function(line_item) { return line_item.sku; }).join(','),
    };
    return url.replace(/\{(\w+)\}/g, function(matches, substitution) {
        return window.encodeURIComponent(substitutions[substitution] || '') || substitution;
    });
};

theme.bsFormValidate = function(form){
    var result =  form.checkValidity();
    $(form).addClass('was-validated');
    $(form).find('.btn-group-toggle').parents('.form-group').removeClass('has-errors');
    $(form).find('.btn-group-toggle input[type=radio]:invalid').parents('.form-group').addClass('has-errors');
    $(form).find('.btn-group-toggle input[type=number]:invalid').parents('.form-group').addClass('has-errors');
    theme.product.checkValidity(form);
    return result;
};

theme.bsFormResetValidation = function(form){
    $(form).removeClass('was-validated');
};

$(document).ready(function(){

    var token = $('meta[name="csrf-token"]').text();
    if (token) {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': token }
        });
    }

    new WOW().init({
        'offset'   : 20,
        'duration' : '1s',
        'mobile'   : false
    });

    $('body').on('click', 'a[href^="#"]:not([data-toggle],[data-slide])', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        if (href !== '#') {
            var anchor = $(href + ',a[name="' + href.substr(1) + '"]');
            theme.scrollIntoView(anchor, 40, 1500, 'ease-in-out').catch(theme.noop);
        }
    });

    $('.odometer[data-odometer-update]').appear(function(e) {
        this.innerHTML = $(this).data('odometer-update');
    });

    $('a[data-video-id]').each(function() {
        $(this).modalVideo({
            channel: $(this).data('channel') || 'youtube'
        });
    });

    $('a[data-video-url]').modalVideo();

    $('.flipclock[data-countdown]').each(function() {
        var d = Sugar.Date.create($(this).data('countdown'));
        var seconds = isNaN(d) ? 0 : Math.max(0, Sugar.Date.secondsFromNow(d));
        $(this).FlipClock(seconds, {
            clockFace: 'DailyCounter',
            countdown: true,
            showSeconds: !!$(this).data('show-seconds')
        });
    });

    $('[data-toggle="tooltip"]').tooltip();

    function applyMasonry(selector, columns) {
        var options = {
            container: selector,
            trueOrder: true,
            waitForImages: true,
            margin: 32,
            columns: columns || 3,
            breakAt: {
                768: 2,
                576: 1
            }
        };
        $(selector).addClass('macyjs').each(function() {
            Macy(options);
        });
    }

    applyMasonry('.masonry-2', 2);
    applyMasonry('.masonry-3', 3);
    applyMasonry('.masonry-4', 4);

    function checkIfStickyDivShouldShow() {
        var scrollTop = $(this).scrollTop();
        $('.sticky-div').each(function (_, div) {
            var $div = $(div);
            var $showAfter = $($div.data('sticky-after'));
            var offset = $showAfter.offset();
            if (offset.top >= scrollTop) {
                $div.addClass('hide-important');
            } else {
                $div.removeClass('hide-important');
            }
        });
    }

    if ($('.sticky-div').length > 0) {
        /*
            NOTE:
            *Debouncing* poses issues because on some mobile phones, they utilize
            "smooth scroll" which means that scrolling lasts a looooong time and
            so the sticky takes a long time to show/hide

            *Throttling* also poses issues because it triggers only once in the time
            frame. If it triggers while your sticky "shouldn't show" but you keep
            scrolling and stop within the time frame, it won't trigger again, so the
            sticky that should show, won't

            Given these scenarios, I've (Kory) opted to add both. Better than it
            executing on every scroll event, there is probably a better solution
            out there but I've hit the limit of how much time I want to invest.
        */
        $(window).scroll(Sugar.Function.debounce(checkIfStickyDivShouldShow, 300));
        $(window).scroll(Sugar.Function.throttle(checkIfStickyDivShouldShow, 300));
        checkIfStickyDivShouldShow();
    }

    $('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
        var $el = $(this);
        var $parent = $(this).offsetParent('.dropdown-menu');
        if (! $(this).next().hasClass('show')) {
            $(this).parents('.dropdown-menu').first().find('.show').removeClass('show');
        }
        $(this).next('.dropdown-menu').toggleClass('show');
        $(this).toggleClass('show');
        $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
            $('.dropdown-menu .show').removeClass('show');
        });
        if (! $parent.parent().hasClass('nav')) {
            $el.next().css({ top: $el[0].offsetTop - 10, left: $parent.outerWidth() - 4 });
        }
        return false;
    });

    $('.add-sponsorship-options input[name="payment_option_amount"]').on('click', function(e) {
        e.stopPropagation();
        var $this = $(this), $parent = $this.parents('.btn');
        $parent.find('input[type=radio]').click();
        $this.focus();
    });

    $('.add-sponsorship-options input[name="payment_option_id"]').on('change', function(e) {
        $('.add-sponsorship-options input[name="payment_option_amount"]').val('');
        if (!$(this).parents('.btn').hasClass('is-custom')) {
            $(this).parents('.btn-group-toggle').find('input.form-control').val('');
        }
    });

    $('.social-actions a.btn:not([href^=mailto])').on('click', function (e) {
        e.preventDefault();
        window.open(this.href, '_blank', 'height=300,width=550,resizable=1');
    });

    $(document).on('click', '[data-toggle="lightbox"]', function(ev) {
        ev.preventDefault();
        $(this).ekkoLightbox();
    });

    $('.read-more').on('click', function(ev){
        ev.preventDefault();
        $($(this).attr('href')).addClass('read-more-content-open');
        $(this).remove();
    });

    $('.search-toggle').on('click', function(e) {
        e.preventDefault();
        var $search = $('.site-search').addClass('active');
        setTimeout(function() {
            $search.find('input[type=text]').focus();
        }, 250);
    });

    $('.site-search__close').on('click', function(e) {
        e.preventDefault();
        $('.site-search').removeClass("active");
    });

    $('.quill-editor').each(function(i, el){
        var quill = new Quill(el, {
            modules: {
                toolbar: [
                    [{ header: [1, 2, false] }],
                    ['bold', 'italic'],
                    ['link']
                ]
            },
            placeholder: theme.trans('scripts.theme.compose_an_epic'),
            theme: 'snow'  // or 'bubble'
        });

        var delta = quill.clipboard.convert($('#' + $(el).data('input')).val())
        quill.setContents(delta, 'silent');

        $(el).parents('form').first().on('submit',function(ev){
            $('#' + $(el).data('input')).val($(el).find('.ql-editor').html());
        });
    });

    $('form[data-confirm]').each(function(i,form){
        $(form).on('submit',function(ev){
            if (!$(ev.target).data('is_confirmed')) {
                var $modal = $('<div class="modal fade" id="form-confirm-modal" tabindex="-1" role="dialog" aria-labelledby="form-confirm-modal-label" aria-hidden="true">' +
                    '<div class="modal-dialog" role="document">' +
                        '<div class="modal-content">' +
                            '<div class="modal-header">' +
                                '<h5 class="modal-title">' + theme.trans('scripts.theme.confirm') + '</h5>' +
                                '<button type="button" class="close" data-dismiss="modal" aria-label="' + theme.trans('general.actions.close') + '">' +
                                    '<span aria-hidden="true">&times;</span>' +
                                '</button>' +
                            '</div>' +
                            '<div class="modal-body">' + $(ev.target).data('confirm') + '</div>' +
                            '<div class="modal-footer">' +
                                '<button type="button" data-dismiss="modal" class="btn-confirm btn btn-outline-primary"><i class="fa fa-check"></i> ' + theme.trans('scripts.theme.yes') + '</button>' +
                                '<button type="button" data-dismiss="modal" class="btn btn-primary"><i class="fa fa-times"></i> ' + theme.trans('scripts.theme.no') + '</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>')
                    .data('original_form', ev.target)
                    .appendTo('body')
                    .modal();

                $modal.find('.btn-confirm').on('click', function(ev){
                    $($(ev.target)
                        .parents('.modal')
                        .first()
                        .data('original_form'))
                            .data('is_confirmed', true)
                            .submit();
                });

                ev.preventDefault();
            }
        });
    });

    // Update the DOM when changes are made to the account
    Givecloud.Account.subscribe(function(account) {
        if (document.getElementById('nav-create-account')) {
            $('#nav-create-account').remove();
            $('#nav-signin').replaceWith([
                '<li class="nav-item">',
                '    <a class="nav-link" href="#">',
                '        ' + theme.trans('scripts.theme.hi_name', { name: account.display_name, icon: '<i class="fa fa-fw fa-user-circle-o"></i>' }) + ' &nbsp;',
                '    </a>',
                '</li>',
            ].join(''));
        }
    });

    // Update the DOM when changes are made to the cart
    Givecloud.Cart().subscribe(function(cart) {
        $('.-cart-count').each(function(){
            this.innerHTML = cart.line_items.length;
        });

        if ($('body').hasClass('show-cart-preview')) {
            theme.cartPreview(cart);
        }
    });

    $('#nav-signin form').on('submit', function(e){
        e.preventDefault();

        var email = $(this).find('input[name=email]').val();
        var password = $(this).find('input[name=password]').val();
        var remember_me = $(this).find('input[name=remember_me]').prop('checked');

        var btn = Ladda.create(this.querySelector('button[type=submit]'));
        btn.start();

        Givecloud.Account.login(email, password, remember_me)
            .then(function(data) {
                top.location.href = data.redirect_to;
            }).catch(function(err) {
                theme.toast.error(err);
            }).finally(function() {
                 btn.stop();
            });
    });


    $('form[name=registerForm]').on('submit', function(e){
        e.preventDefault();

        if (!theme.bsFormValidate(this)) {
            return;
        }

        var btn = Ladda.create(this.querySelector('button[type=submit]'));
        var err = $(this).find('.alert-danger').hide();

        var data = {
            'account_type_id'      : $(this).find('select[name=account_type_id]').val(),
            'organization_name'    : $(this).find('input[name=organization_name]').val(),
            'donor_id'             : $(this).find('input[name=donor_id]').val(),
            'title'                : $(this).find('select[name=title]').val(),
            'first_name'           : $(this).find('input[name=first_name]').val(),
            'last_name'            : $(this).find('input[name=last_name]').val(),
            'email'                : $(this).find('input[name=email]').val(),
            'zip'                  : $(this).find('input[name=zip]').val(),
            'password'             : $(this).find('input[name=password]').val(),
            'email_opt_in'         : $(this).find('input[name=email_opt_in]:checked').val(),
            'g-recaptcha-response' : $(this).find('textarea[name=g-recaptcha-response]').val()
        };

        btn.start();

        Givecloud.Account.signup(data)
            .then(function(data) {
                top.location.href = data.redirect_to;
            }).catch(function(error) {
                grecaptcha.reset();
                err.html(theme.error(error)).show();
            }).finally(function() {
                 btn.stop();
            });
    });

    $('form[name=registerForm] select[name=account_type_id]').change(function(e) {
        var container = $('form[name=registerForm] input[name=organization_name]').parents('.form-group');
        container[$(this).find('option:selected').data('organization') ? 'show' : 'hide']();
    }).trigger('change');


    $('form[name=loginForm]').on('submit', function(e){
        e.preventDefault();

        if (!theme.bsFormValidate(this)) {
            return;
        }

        var btn = Ladda.create(this.querySelector('button[type=submit]'));
        var err = $(this).find('.alert-danger').hide();

        var data = {
            success_url    : $(this).find('input[name=success_url]').val(),
            email          : $(this).find('input[name=email]').val(),
            password       : $(this).find('input[name=password]').val()
        };

        btn.start();

        Givecloud.Account.login(data.email, data.password)
            .then(function(res) {
                top.location.href = data.success_url || res.redirect_to;
            }).catch(function(error) {
                btn.stop();
                err.html(theme.error(error)).show();
            });
    });


    $('form[name=resetPasswordForm]').on('submit', function(e){
        e.preventDefault();

        if (!theme.bsFormValidate(this)) {
            return;
        }

        var btn = Ladda.create(this.querySelector('button[type=submit]'));
        var msg = $(this).find('.alert-success').hide();
        var err = $(this).find('.alert-danger').hide();

        var data = {
            email: $(this).find('input[name=email]').val(),
        };

        btn.start();

        Givecloud.Account.resetPassword(data.email)
            .then(function(data) {
                msg.html(data.message).show();
            }).catch(function(error) {
                err.html(theme.error(error)).show();
            }).finally(function() {
                 btn.stop();
            });
    });

    $('form[action^="/ds/form/"]').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        function resetCaptcha() {
            var captchaType = Givecloud.config.captcha_type || 'recaptcha';
            if (captchaType === 'hcaptcha') {
                hcaptcha.reset();
            } else if (captchaType === 'recaptcha') {
                grecaptcha.reset();
            }
        }
        var $alert = $form.find('.alert').hide();
        if ($alert.length === 0) {
            $alert = $('<div class="alert">').hide().prependTo($form);
        }
        $.post($form.attr('action'), $form.gc_serializeArray())
            .done(function(res) {
                if (res.redirect_to) {
                    top.location.href = res.redirect_to;
                } else {
                    $form[0].reset();
                    resetCaptcha();
                    $alert.removeClass().addClass('alert alert-success').text(res.message).show();
                    theme.scrollIntoView($alert, 85);
                }
            }).fail(function(res) {
                if (res.redirect_to) {
                    top.location.href = res.redirect_to;
                } else {
                    resetCaptcha();
                    $alert.removeClass().addClass('alert alert-danger').text(res.responseJSON.message || res.responseJSON.error).show();
                    theme.scrollIntoView($alert, 85);
                }
            });
    });

    $('form[action="/ds/form/signup"] input[name="country"]').each(function() {
        var $input = $(this);
        Givecloud.Services.Locale.countries().then(function(data) {
            var countries = [];
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
            var $select = $('<select class="form-control" name="country"><option disabled selected>' + theme.trans('general.forms.country') + '</option></select>');
            countries.forEach(function(country) {
                var $option = $('<option></option>');
                $option.attr('value', country.value).html(country.label);
                $select.append($option);
            });
            $input.replaceWith($select);
        });
    });

    $('form[name=changePasswordForm]').on('submit', function(e){
        e.preventDefault();

        if (!theme.bsFormValidate(this)) {
            return;
        }

        var btn = theme.ladda.start(this.querySelector('button[type=submit]'));
        var data = {
            password: $(this).find('input[name=password]').val(),
            password_confirmation: $(this).find('input[name=password_confirmation]').val(),
        };

        Givecloud.Account.changePassword(data)
            .then(function(data) {
                top.location.href = data.redirect_to;
            }).catch(function(error) {
                theme.toast.error(error);
            }).finally(function() {
                 btn.stop();
            });
    });


    /**
     * Initialize AniView
     * Animates elements into view as the user scrolls.
     */
    //$('.aniview').AniView();


    /**
     * Sidebar Nav
     * Functions that help manage the sidebar nav
     *
     * Usage
     * - Use the class `.side-bar` on the element you want to make a sidebar
     * - This JS will keep the side-bar full height
     * - Anything with the class .side-bar-open and .side-bar-close will open
     *   and close the sidebar
     * - A sidebar that is open will have the `.open` class added. By default
     *   a sidebar is closed (no .open class)
     */
    (function(){
        // control height
        function resizeSB(){
            $('.side-bar').height($(window).height()+'px');
        }
        resizeSB(); // initial resize
        $(window).on('resize', resizeSB); // anytime the page is resized

        // open/close
        $('.side-bar-open, .side-bar-close').on('click',function(){
            $('body').toggleClass('side-bar-open');
            $('.side-bar').toggleClass('open');
        });
    })();

    /**
     * Headroom (requires Headroom.js)
     * Expands/contracts headers based on scroll position.
     *
     * Usage
     * (Inspect accompanying CSS)
     */
    (function(){
        var element = $('header').get(0);
        if (element) {
            var headroom = new Headroom(element, {
                offset: 100,
                tolerance: 10
            });
            headroom.init();
        }
    })();

    /**
     * Adding Sponsorship to Cart
     * Find all forms where we're adding sponsorships to cart
     * and add the appropriate behaviour.
     *
     * Usage
     * - Give any form the class 'add-sponsorship-form' to
     *   processes sponsorship additions to cart
     */
    (function(){

        function onSubmit(ev){
            var $form = $(ev.target),
                $option = $form.find('input[name=payment_option_id]:checked'),
                data = {
                    'sponsorship_id'    : $form.find('input[name=sponsorship_id]').val(),
                    'payment_option_id' : $option.val(),
                    'payment_option_amount' : $option.parents('.btn').find('input[name=payment_option_amount]').val(),
                    'initial_charge'    : $form.find('input[name=initial_charge]:checked,input[name=initial_charge][type=hidden]').val()
                },
                submit_btn = Ladda.create($form.find('button[type=submit]')[0]),
                $opts = $form.find('.add-sponsorship-options,.add-sponsorship-btns'),
                $success = $form.find('.add-sponsorship-success');

            // stop default form behaviour
            ev.preventDefault();

            // loading button
            submit_btn.start();

            Givecloud.Cart().addSponsorship(data)
                .then(function(checkout){
                     $opts.slideUp();
                     $success.slideDown();
                })
                .catch(function(err){
                    theme.toast.error(err);
                    //theme.toast.error("Oh no! There was a problem. Can you try again?\n\nIf it still doesn't work, can you contact us and let us know right away?! Thank you!");
                }).finally(function(err){
                     submit_btn.stop();
                });
        }

        $('.add-sponsorship-form').bind('submit', onSubmit);
    })();

    /**
     * Carousel
     */
    $(".owl-carousel").each(function(i, el){
        var $el = $(el);
        var items = $el.data('items') || 5;

        if ($el.hasClass('owl-center-zoom')) {
            $el.owlCarousel({
                center: true,
                autoHeight: true,
                nav: false,
                loop: true,
                margin: 10,
                autoplay: true,
                autoplayTimeout: 6000,
                autoplayHoverPause: true,
                responsiveClass: true,
                responsive:{
                    0: {
                        items: Math.min(items, 1),
                        nav: true
                    },
                    640: {
                        items: Math.min(items, 3),
                        nav: true
                    },
                    992: {
                        items: Math.min(items, 3),
                        nav: true
                    },
                }
            });

        } else {
            $el.owlCarousel({
                nav: false,
                loop: true,
                margin: 30,
                autoplay: true,
                autoplayTimeout: 3000,
                autoplayHoverPause: true,
                responsiveClass: true,
                responsive:{
                    0: {
                        items: Math.min(items, 2),
                        nav: true
                    },
                    640: {
                        items: Math.min(items, 3),
                        nav: true
                    },
                    992: {
                        items: Math.min(items, 5),
                        nav: true
                    },
                }
            });
        }
    });

    /**
     * Date Picker
     */
    (function(){
        $('.date-picker').datepicker({
            format: theme.trans('date_formats.calendar_short_month_day_year'),
            autoclose: true
        }).on('show', function(event) {
            $(this).data('datepicker').picker.addClass('show');
        }).on('hide', function(event) {
            event.preventDefault();
            event.stopPropagation();
            $(this).data('datepicker').picker.removeClass('show');
        });
    }());

    /**
     * Cookie Notice
     */
    (function() {
        var $alert = jQuery('#cookie-alert');

        if (!$alert.length || Cookies.get('acceptCookies')) {
            return;
        }

        $alert.on('click', 'button', function() {
            $alert.removeClass('show');
            Cookies.set('acceptCookies', true, 60);
        }).show();

        setTimeout(function() {
            $alert.addClass('show');
        });
    })();

    /**
     * Pop-Up
     */
    (function() {
        $('[data-pop-up-id]').each(function(i,e){
            var $pop      = $(e),
                pop_id    = "gc_popup_" + ($pop.data('pop-up-id') || 'mypopup').replace(/[^0-9a-zA-Z]/g, ''),
                pop_delay = parseInt($(e).data('pop-up-delay') || 0) * 1000;

            if (Cookies.get(pop_id)) {
                return;
            }

            $pop.modal({
                'backdrop':'static',
                'show':false
            })
            .on('click', '.close-pop-up', function(ev) {
                ev.preventDefault();
                Cookies.set(pop_id, true, 60);
                if ($(this).attr('href')) {
                    window.location = $(this).attr('href');
                }
                $(this).modal('hide');
            })
            .on('hidden.bs.modal', function(ev) {
                Cookies.set(pop_id, true, 60);
                $(this).modal('dispose');
            });

            setTimeout(function(){
                $pop.modal('show');
            }, pop_delay);
        });
    })();

    /**
     * Image options
     */
    $.imageOptions();

    /**
     * NPS capture
     */
    $('form[name=capture-nps]').on('submit', function(ev){
        ev.preventDefault();

        var $form = $(this),
            nps = $form.find('input[name=nps]:checked').val(),
            btn = Ladda.create(this.querySelector('button[type=submit]'));

        if (isNaN(parseInt(nps))) {
            return theme.toast.error(theme.trans('scripts.theme.choose_a_number'));
        }

        btn.start();

        Givecloud.Account.update({ nps: nps })
            .then(function() {
                $('.capture-nps-wrap').remove();
                theme.toast.success(theme.trans('scripts.theme.thank_you_for_the_feedback'));
            }).catch(function(err) {
                theme.toast.error(err);
            }).finally(function() {
                btn.stop();
            });
    });

    // Admin Bar for logged in users
    var $adminLinksOpenBtn = $('#admin-actions-anchor');
    var $adminLinksCloseBtn = $('#admin-actions-close');
    var $adminLinksActionPanel = $('#admin-actions-panel');

    $adminLinksOpenBtn.on('click', function(e) {
        e.preventDefault();
        $adminLinksActionPanel.removeClass('d-none slide-out-right');
        $adminLinksActionPanel.addClass('d-flex slide-in-right');
        $adminLinksOpenBtn.removeClass('slide-in-right');
        $adminLinksOpenBtn.addClass('slide-out-right');
    });

    $adminLinksCloseBtn.on('click', function(e) {
        e.preventDefault();
        $adminLinksActionPanel.removeClass('slide-in-right');
        $adminLinksActionPanel.addClass('slide-out-right');
        $adminLinksOpenBtn.removeClass('slide-out-right');
        $adminLinksOpenBtn.addClass('slide-in-right');
    });


});

$.imageOptions = function(){
    var onChoose = function(target){
        $(target).find('input[type=radio]').prop('checked',true);
        $(target).parents('.image-options').first().find('.image-option').removeClass('image-option-selected');
        $(target).addClass('image-option-selected');
    }

    $('.image-option').on('click', function(ev){ onChoose(ev.target); });

    $('.image-option-input').each(function(i, el){
        var $el = $(el),
            $label = $('label[for='+$el.attr('id')+']');

        var _showImage = function (src) {
            var fr = new FileReader();
            fr.onload = function(e) {
                var $option = $label.parents('.image-option-custom');
                $option.css('background-image', "url('"+this.result+"')");
                onChoose($option);
            };
            src.addEventListener("change",function() {
                if (src.files.length > 0) {
                    fr.readAsDataURL(src.files[0]);
                }
            });
        };

        if ($el.data('selected-image')) {
            $label.parents('.image-option-custom').css('background-image', "url('"+$el.data('selected-image')+"')");
        }

        _showImage(el);
    });

    if ($('.image-option input[type=radio]:checked').length > 0) {
        $('.image-option input[type=radio]:checked').first().parent().click();
    } else {
        $('.image-option').first().click();
    }
}


jQuery.fn.gc_serializeArray = function() {
    var fields = jQuery.fn.serializeArray.apply(this);
    jQuery.each(this.find('input'), function (i, element) {
        if (element.type == 'checkbox' && !element.checked) {
            fields.push({ name: element.name, value: '' })
        }
    });
    return fields;
};


theme.modal = function(settings) {
    var opts = jQuery.extend({}, {
            id: 'themeModal-' + Math.floor((Math.random() * 100000) + 1),
            title: 'Loading...',
            class: '',
            body: '<div style="padding:20px; text-align:center;"><i class="fa fa-spinner fa-spin"></i></div>',
            buttons: [
                '<button type="button" class="btn btn-light" data-dismiss="modal">' + theme.trans('general.actions.close') + '</button>'
            ],
            backdrop: true,
            onOpen: null,
            size: 'lg'
        }, settings);

    var $modal = $('<div id="' + opts.id + '" class="modal fade ' + opts.class + '" tabindex="-1" role="dialog">' +
            '<div class="modal-dialog modal-' + opts.size + '" role="document">' +
                '<div class="modal-content">' +
                    '<div class="modal-header">' +
                        '<h4 class="modal-title">'+ opts.title +'</h4>' +
                        '<button type="button" class="close" data-dismiss="modal" aria-label="' + theme.trans('general.actions.close') + '"><span aria-hidden="true">&times;</span></button>' +
                    '</div>' +
                    '<div class="modal-body">' +
                        opts.body +
                    '</div>' +
                    '<div class="modal-footer">' +
                        opts.buttons.join('') +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>');

    $('body').append($modal);

    $modal.on('hidden.bs.modal', function () {
        $modal.remove();
    });

    $modal.on('show.bs.modal', function (ev) {
        $modal.find('.modal-dialog').velocity('callout.tada', {duration:300});
    });

    $modal.on('shown.bs.modal', function (ev) {
        $modal.find('[autofocus]').first().focus();
        if (typeof opts.onOpen === 'function') opts.onOpen($modal);
    });

    $modal.modal({
        backdrop: opts.backdrop
    });

    if (opts.backdropColour) {
        $modal.data('bs.modal')._backdrop.style.background = opts.backdropColour;
    }

    return $modal;
}


theme.alert = function(message, modalClass, icon, backdropColour){
    if (typeof modalClass === 'undefined') modalClass = 'light';
    if (typeof icon === 'undefined') icon = 'fa-question-circle';

    if (!backdropColour) {
        if (modalClass === 'danger') backdropColour = '#690202';
        if (modalClass === 'warning') backdropColour = '#fcf8e3';
    }

    return theme.modal({
        class: 'modal-' + modalClass,
        size: 'sm',
        body: message,
        title: '<i class="fa ' + icon + '"></i> ' + theme.trans('scripts.theme.alert'),
        buttons: [
            '<button type="button" class="btn btn-light" data-dismiss="modal"><i class="fa fa-times"></i> ' + theme.trans('general.actions.close') + '</button>'
        ],
        backdropColour: backdropColour || '#690202'
    });
};


theme.confirm = function(message, action, modalClass, icon, backdropColour) {
    if (typeof modalClass === 'undefined') modalClass = 'light';
    if (typeof icon === 'undefined') icon = 'fa-question-circle';

    if (!backdropColour) {
        if (modalClass === 'danger') backdropColour = '#690202';
        if (modalClass === 'warning') backdropColour = '#fcf8e3';
    }

    return theme.modal({
        class: 'modal-' + modalClass,
        size: 'sm',
        body: message,
        title: '<i class="fa ' + icon + '"></i> ' + theme.trans('scripts.theme.confirm'),
        buttons: [
            '<button type="button" class="btn btn-' + modalClass + ' confirm-do" data-dismiss="modal"><i class="fa fa-check"></i> ' + theme.trans('scripts.theme.yes') + '</button>',
            '<button type="button" class="btn btn-light" data-dismiss="modal"><i class="fa fa-times"></i> ' + theme.trans('scripts.theme.no') + '</button>'
        ],
        backdropColour: backdropColour,
        onOpen: function (element) {
            if (typeof action === 'function') {
                jQuery(element).find('.confirm-do').click(function(e) {
                    action(element);
                });
            }
        }
    });
};

theme.fundraiserOnAccountTypeChange = function(element) {
    var $accountType = jQuery(element);
    var $organizationName = $accountType.parents('.modal-body').find('input[name=organization_name]').parents('.form-group');
    if ($accountType.find('option:selected').data('organization')) {
        $organizationName.show().attr('required', true);
    } else {
        $organizationName.hide().removeAttr('required');
    }
};

window.googleMapsLoaded = new Promise(function(resolve, reject) {
    window.loadLookup = function() { resolve(); };
});
