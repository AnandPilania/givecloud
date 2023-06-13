import Endpoint from '@core/endpoint'
import CardholderData from '@core/cardholder-data'

class Gateway extends Endpoint {
  $cardholderData(data) {
    return new CardholderData(data, this.$app.config.supported_cardtypes)
  }

  $capture(cart, payment_type = 'credit_card', recaptchaResponse, savePaymentMethod, data = {}) {
    return this.$http('POST', `carts/${cart.id}/capture`, {
      context: this.$app.config.context,
      provider: this.$providerName || this.$name,
      ...(this.$app.config.testmode_token && { testmode_token: this.$app.config.testmode_token }),
      payment_type,
      save_payment_method: !!savePaymentMethod,
      'g-recaptcha-response': recaptchaResponse,
      ...data,
    })
  }

  $generateRandomToken(prefix = '') {
    return prefix + Math.random().toString(36).substring(2, 15)
  }

  $tokenize(paymentMethod, payment_type = 'credit_card', recaptchaResponse, data = {}) {
    return this.$http('POST', `account/payment-methods/${paymentMethod.id}/tokenize`, {
      context: this.$app.config.context,
      payment_type,
      'g-recaptcha-response': recaptchaResponse,
      ...data,
    })
  }

  canSavePaymentMethods() {
    return true
  }

  usesHostedPaymentFields() {
    return false
  }

  usesAchHostedPaymentFields() {
    return false
  }

  canMakePayment() {
    return Promise.resolve(null)
  }

  canMakeAchPayment(currencyCode) {
    return !!this.$currenciesSupportingAch?.find((code) => code === currencyCode)
  }

  chargeCaptureToken(cart, token) {
    return this.$http('POST', `carts/${cart.id}/charge`, {
      provider: this.$providerName || this.$name,
      token,
      visitor: this.$app.Analytics.$getVisitorId(),
    })
  }

  connectSourceToken(paymentMethod, token) {
    return this.$http('POST', `account/payment-methods/${paymentMethod.id}/connect`, { token })
  }
}

export default Gateway
