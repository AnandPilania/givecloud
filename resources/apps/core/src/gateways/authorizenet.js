import Gateway from '@core/gateway'

class AuthorizeNetGateway extends Gateway {
  constructor(app) {
    super(app)

    this.$name = 'authorizenet'
    this.$displayName = 'Authorize.Net'
    this.$currenciesSupportingAch = ['USD']

    this.apiLoginId = ''
    this.clientKey = ''
  }

  setConfig({ api_login_id, client_key }) {
    this.apiLoginId = api_login_id
    this.clientKey = client_key
  }

  getCaptureToken(
    cart,
    cardholderData,
    paymentType = 'credit_card',
    recaptchaResponse = null,
    savePaymentMethod = false
  ) {
    if (paymentType === 'none') {
      return Promise.resolve(this.$generateRandomToken('none_'))
    }

    try {
      cardholderData = this.getCardholderData(cardholderData, paymentType)
    } catch (err) {
      return Promise.reject(err)
    }

    return this.$capture(cart, paymentType, recaptchaResponse, savePaymentMethod).then(() =>
      this.dispatchData(cardholderData, paymentType)
    )
  }

  getSourceToken(paymentMethod, cardholderData, paymentType = 'credit_card', recaptchaResponse = null) {
    if (paymentType === 'none') {
      return Promise.resolve(this.$generateRandomToken('none_'))
    }

    try {
      cardholderData = this.getCardholderData(cardholderData, paymentType)
    } catch (err) {
      return Promise.reject(err)
    }

    return this.$tokenize(paymentMethod, paymentType, recaptchaResponse).then(() =>
      this.dispatchData(cardholderData, paymentType)
    )
  }

  dispatchData(cardholderData, paymentType) {
    var secureData = {
      authData: {
        clientKey: this.clientKey,
        apiLoginID: this.apiLoginId,
      },
    }

    if (paymentType === 'bank_account') {
      secureData.bankData = cardholderData
    } else {
      secureData.cardData = cardholderData
    }

    return new Promise((resolve, reject) => {
      this.$app.$window.Accept.dispatchData(secureData, (response) => {
        if (response.messages.resultCode === 'Error') {
          if (Array.isArray(response.messages.message) && response.messages.message.length > 0) {
            var error = response.messages.message[0]
            reject(`${error.text} (Code: ${error.code})`)
          } else {
            console.error(response)
            reject('Invalid token')
          }
        } else {
          resolve(response.opaqueData.dataValue)
        }
      })
    })
  }

  getCardholderData(cardholderData, paymentType) {
    cardholderData = this.$cardholderData(cardholderData)

    if (paymentType === 'bank_account') {
      cardholderData.requireACH()

      const data = {
        nameOnAccount: cardholderData.account_holder_name || '',
        accountNumber: cardholderData.account_number || '',
        routingNumber: cardholderData.routing_number || '',
        accountType: cardholderData.account_type || '',
      }

      if (cardholderData.account_holder_type === 'business') {
        data.accountType = 'businessChecking'
      }

      return data
    }

    cardholderData.requireCreditCard()

    return {
      fullName: cardholderData.name || '',
      cardNumber: cardholderData.number || '',
      cardCode: cardholderData.cvv || '',
      month: cardholderData.exp_month || '',
      year: cardholderData.exp_year || '',
      zip: cardholderData.address_zip || '',
    }
  }
}

export default AuthorizeNetGateway
