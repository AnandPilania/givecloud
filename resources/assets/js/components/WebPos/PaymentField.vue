
<template>
    <div>
        <div v-if="usesHostedPaymentFields" :id="field" class="form-control input-lg"></div>
        <slot v-else></slot>
    </div>
</template>

<script>
export default {
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
}
</script>
