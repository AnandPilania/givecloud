import Gateway from '@core/gateway'

class PaymentMethodGateway extends Gateway {
  constructor(app) {
    super(app)

    this.$name = 'paymentmethod'
  }

  getCaptureToken(cart, cardholderData, paymentType = 'credit_card', recaptchaResponse = null) {
    if (paymentType === 'none') {
      return Promise.resolve(this.$generateRandomToken('none_'))
    }

    return this.$http('POST', `carts/${cart.id}/capture`, {
      provider: this.$name,
      payment_type: 'payment_method',
      payment_method: cardholderData.payment_method,
      'g-recaptcha-response': recaptchaResponse,
    }).then((response) => response.token_id)
  }
}

export default PaymentMethodGateway
