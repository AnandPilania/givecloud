import Endpoint from '@core/endpoint'

class PaymentMethodsEndpoint extends Endpoint {
  get() {
    return this.$http('GET', `account/payment-methods`)
  }

  create(paymentMethod) {
    return this.$http('POST', `account/payment-methods`, paymentMethod)
  }

  setDefault(id) {
    return this.$http('POST', `account/payment-methods/${id}/default`)
  }

  useForSubscriptions(id, subscriptions) {
    return this.$http('POST', `account/payment-methods/${id}/subscriptions`, { subscriptions })
  }

  remove(id) {
    return this.$http('DELETE', `account/payment-methods/${id}`)
  }

  tokenize(paymentMethod, cardholderData, paymentType = 'credit_card') {
    var provider = paymentMethod.payment_provider.provider
    return this.$app
      .Gateway(provider)
      .getSourceToken(paymentMethod, cardholderData, paymentType)
      .then((token) => {
        return this.$app.Gateway(provider).connectSourceToken(paymentMethod, token)
      })
  }
}

export default PaymentMethodsEndpoint
