import axios from 'axios'
import Gateway from '@core/gateway'

class VancoGateway extends Gateway {
  constructor(app) {
    super(app)

    this.$name = 'vanco'
    this.$displayName = 'Vanco Payment Solutions'
    this.$currenciesSupportingAch = ['USD']
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
      this.getCardholderData(cardholderData, paymentType)
    } catch (err) {
      return Promise.reject(err)
    }

    return this.$capture(cart, paymentType, recaptchaResponse, savePaymentMethod)
      .then((capture) => this.doEFTTransparentRedirect(capture, cardholderData, paymentType))
      .then((response) => response.token_id)
  }

  getSourceToken(paymentMethod, cardholderData, paymentType = 'credit_card', recaptchaResponse = null) {
    if (paymentType === 'none') {
      return Promise.resolve(this.$generateRandomToken('none_'))
    }

    try {
      this.getCardholderData(cardholderData, paymentType)
    } catch (err) {
      return Promise.reject(err)
    }

    return this.$tokenize(paymentMethod, paymentType, recaptchaResponse)
      .then((source) => this.doEFTTransparentRedirect(source, cardholderData, paymentType))
      .then((response) => response.token_id)
  }

  getCardholderData(cardholderData, paymentType, data = {}) {
    cardholderData = this.$cardholderData(cardholderData)

    if (paymentType === 'bank_account') {
      cardholderData.requireACH()
      data.accounttype = cardholderData.account_type === 'savings' ? 'S' : 'C'
      data.accountnumber = cardholderData.account_number
      data.routingnumber = cardholderData.routing_number
    } else {
      cardholderData.requireCreditCard()
      data.accounttype = 'CC'
      data.name_on_card = cardholderData.name
      data.accountnumber = cardholderData.number
      data.expmonth = cardholderData.exp_month
      data.expyear = cardholderData.exp_year
    }

    return data
  }

  doEFTTransparentRedirect(res, cardholderData, paymentType) {
    let params = this.getCardholderData(cardholderData, paymentType, res.data)

    for (var i in params) {
      if (params[i] === null || params[i] === 'null') params[1] = ''
    }

    return this.$jsonp(res.url, params).then((data) => {
      if (!data.urltoredirect) {
        return Promise.reject('Unknown error (Code: 422)')
      }
      return axios.post(data.urltoredirect, data)
    })
  }
}

export default VancoGateway
