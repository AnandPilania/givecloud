<template>
    <div class="credit-card-fields">
        <slot></slot>
    </div>
</template>

<script>
export default {
    computed: {
        gateway: function () {
            return Givecloud.PaymentTypeGateway('credit_card');
        },
    },
    mounted: function () {
        this.$nextTick(this.setupFields.bind(this));
    },
    methods: {
        setupFields: function () {
            switch (this.gateway.$name) {
                case 'braintree':
                    return this.setupPaysafeFields();
                case 'paysafe':
                    return this.setupPaysafeFields();
                case 'stripe':
                    return this.setupStripeFields();
            }
        },
        setupPaysafeFields: function () {
            if (!document.getElementById('inputPaymentNumber')) {
                return;
            }
            this.gateway.setupFields(
                {
                    cardNumber: {
                        selector: '#inputPaymentNumber',
                        placeholder: '0000 0000 0000 0000',
                        separator: ' ',
                    },
                    expiryDate: {
                        selector: '#inputPaymentExpiry',
                        placeholder: 'MM / YY',
                    },
                    cvv: {
                        selector: '#inputPaymentCVV',
                        placeholder: 'CVD',
                        optional: false,
                    },
                },
                {
                    input: {
                        'font-weight': 'normal',
                        'font-size': '18px',
                        'line-height': 1.3333333,
                    },
                }
            );
        },
        setupStripeFields: function () {
            if (!document.getElementById('inputPaymentNumber')) {
                return;
            }
            this.gateway.setupFields(
                {
                    cardNumber: {
                        selector: '#inputPaymentNumber',
                        placeholder: '0000 0000 0000 0000',
                    },
                    cardExpiry: {
                        selector: '#inputPaymentExpiry',
                        placeholder: 'MM / YY',
                    },
                    cardCvc: {
                        selector: '#inputPaymentCVV',
                        placeholder: 'CVD',
                    },
                },
                {
                    base: {
                        fontWeight: 'normal',
                        fontSize: '18px',
                        'line-height': 1.3333333,
                    },
                }
            );
        },
    },
};
</script>
