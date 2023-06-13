import Gateway from '@core/gateway'

class GoCardlessGateway extends Gateway {
  constructor(app) {
    super(app)

    this.$name = 'gocardless'
    this.$displayName = 'GoCardless'
    this.$currenciesSupportingAch = ['EUR', 'GBP', 'SEK']
  }

  getCaptureToken(
    cart,
    cardholderData,
    paymentType = 'bank_account',
    recaptchaResponse = null,
    savePaymentMethod = false
  ) {
    if (paymentType === 'none') {
      return Promise.resolve(this.$generateRandomToken('none_'))
    }

    return new Promise((resolve, reject) => {
      this.$capture(cart, paymentType, recaptchaResponse, savePaymentMethod)
        .then((capture) => {
          top.location.href = capture.url
        })
        .catch((err) => {
          reject(err)
        })
    })
  }

  getSourceToken(paymentMethod, cardholderData, paymentType = 'bank_account', recaptchaResponse = null) {
    if (paymentType === 'none') {
      return Promise.resolve(this.$generateRandomToken('none_'))
    }

    return new Promise((resolve, reject) => {
      this.$tokenize(paymentMethod, paymentType, recaptchaResponse)
        .then((source) => {
          top.location.href = source.url
        })
        .catch((err) => {
          reject(err)
        })
    })
  }
}

export default GoCardlessGateway
