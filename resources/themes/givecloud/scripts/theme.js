
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
                    placeholder: 'MM / YY'
                },
                cvv: {
                    selector: '#inputPaymentCVV',
                    placeholder: '000',
                    optional: false
                }
            });
        },
        setupStripeFields: function() {
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

theme.dataGet = function(data, property, defaultValue) {
    var value = Sugar.Object.get(data, property);
    if (value === '' || value === null || typeof value === 'undefined') {
        return defaultValue;
    } else {
        return value;
    }
};

theme.dataSet = function(data, property, value) {
    Sugar.Object.set(data, property, value);
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

theme.ladda = {
    create: function(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        if (element) {
            return element.ladda = element.ladda || Ladda.create(element);
        }
    },
    start: function(element, holdon) {
        var btn = theme.ladda.create(element);
        if (btn) {
            if (holdon) {
                btn.holdon = true;
                jQuery('body').addClass('holdon');
                if (typeof holdon === 'boolean') {
                    jQuery('body').addClass('holdon-clear');
                }
                jQuery('<div class="holdon-overlay d-flex align-items-center justify-content-center"/>').appendTo('body').append(
                    '<div>' + (typeof holdon === 'string' ? holdon : Array.isArray(holdon) ? holdon.join('') : '') + '</div>'
                );
            }
            return btn && btn.start();
        }
    },
    stop: function(element) {
        var btn = theme.ladda.create(element);
        if (btn) {
            if (btn.holdon) {
                jQuery('body').removeClass(['holdon','holdon-clear']).find('.holdon-overlay').remove();
            }
            return btn && btn.stop();
        }
    }
};

theme.error = function(text) {
    if (Sugar.Object.isError(text) || Sugar.Object.isObject(text)) {
        console.error(text);
        try {
            text = theme.dataGet(text, 'response.data.error')
                || theme.dataGet(text, 'response.data.message')
                || theme.dataGet(text, 'response.message')
                || theme.dataGet(text, 'error.message')
                || theme.dataGet(text, 'message')
                || theme.dataGet(text, 'error')
                || text;
        } catch(err) {
            text = theme.trans('scripts.theme.unknown_error_521');
        }
    }
    return String(text).replace(/[\n\r]/, '<br>');
};

theme.scrollIntoView = function(element, offset, duration, easing) {
    element = $(element);
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

theme.serializeArray = function() {
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
            title: theme.trans('scripts.theme.loading'),
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
};

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
                    action(modal);
                });
            }
        }
    });
};

/**
 * Format an amount to local money format.
 *
 * @param   {float}   amount amount of money to format
 * @param   {string}  currency ISO currency code (CAD)
 * @param   {boolean} showCurrencyCode true if we want to display the currency code
 * @param   {string}  locale ISO locale code (en-CA)
 * @returns {string}  locally formatted amount
 */
theme.money = function (amount, currency, showCurrencyCode, locale) {
    var moneyFormatted = new Intl
        .NumberFormat(locale || Givecloud.config.locale.iso, Object.assign({
            style: "currency",
            currency: currency || Givecloud.config.currency.code,
            currencyDisplay: "symbol", // "narrowSymbol" is not available on all devices
            maximumFractionDigits: 2
        }))
        .format(amount);


        return showCurrencyCode === true
        ? moneyFormatted
        : moneyFormatted.replace(/[A-Z]+/, ""); // remove currency code letters
};

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

window.googleMapsLoaded = new Promise(function(resolve, reject) {
    window.loadLookup = function() { resolve(); };
});
