import Gateway from '@core/gateway'
import { arraySample, dateAddDays } from '@core/utils'

class GivecloudTestGateway extends Gateway {
  constructor(app) {
    super(app)

    this.$name = 'givecloudtest'
    this.$displayName = 'Givecloud Test Gateway'
    this.$currenciesSupportingAch = ['CAD', 'USD']
  }

  canMakePayment() {
    const applePay = !!window.ApplePaySession

    return Promise.resolve({
      applePay,
      googlePay: !applePay,
    })
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

    return this.$capture(cart, paymentType, recaptchaResponse, savePaymentMethod)
      .then((capture) => this.$http('POST', capture.url, cardholderData))
      .then((response) => response.token_id)
  }

  getWalletPayToken() {
    return Promise.resolve({
      token: null,
      customer: {
        name: null,
        email: null,
        phone: null,
      },
    })
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

    return this.$tokenize(paymentMethod, paymentType, recaptchaResponse)
      .then((source) => this.$http('POST', source.url, cardholderData))
      .then((response) => response.token_id)
  }

  getCardholderData(cardholderData, paymentType) {
    // if using wallet pay assign a random test card number and future expiry date
    // to give some varitey to the test transactions
    if (paymentType === 'wallet_pay') {
      cardholderData.number = arraySample([
        '371449635398431',
        '378282246310005',
        '4012888888881881',
        '4111111111111111',
        '5105105105105100',
        '5555555555554444',
      ])

      const expiryDate = dateAddDays(new Date(), arraySample([180, 365, 545, 725]))
      cardholderData.exp_month = ('0' + (expiryDate.getMonth() + 1)).slice(-2)
      cardholderData.exp_year = String(expiryDate.getFullYear()).slice(-2)
    }

    cardholderData = this.$cardholderData(cardholderData)

    if (paymentType === 'bank_account') {
      cardholderData.requireACH()
      return {
        type: paymentType,
        last4: (cardholderData.account_number || '').slice(-4),
        account_type: cardholderData.account_type,
        account_holder_type: cardholderData.account_holder_type,
        routing_number: cardholderData.routing_number,
      }
    }

    cardholderData.requireCreditCard()
    return {
      type: paymentType,
      last4: (cardholderData.number || '').slice(-4),
      expiry: cardholderData.exp,
      brand: cardholderData.brand,
      apple_pay_session: !!window.ApplePaySession,
    }
  }
}

export default GivecloudTestGateway
