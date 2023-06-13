import Deferred from '@core/deferred'
import Gateway from '@core/gateway'

class PayPalCheckoutGateway extends Gateway {
  constructor(app) {
    super(app)

    this.$name = 'paypalcheckout'

    this.clientId = ''
    this.merchantId = ''
    this.environment = 'sandbox'

    this.flow = null
  }

  setConfig({ client_id, merchant_id, environment }) {
    this.clientId = client_id
    this.merchantId = merchant_id
    this.environment = environment || 'sandbox'
  }

  renderButton({ id, style, onPayment, validateForm }) {
    if (!window.paypal) {
      throw new Error('Attempt to render PayPal before PayPal has loaded.')
    }
    let deferred = new Deferred()
    window.paypal.Button.render(
      {
        env: this.environment,
        client: {
          [this.environment]: this.clientId,
        },
        locale: 'en_US',
        style,
        commit: true,
        validate: (actions) => {
          this.P_onValidate(actions, id, deferred, validateForm)
        },
        payment: (data, actions) => {
          return this.P_onPayment(data, actions, onPayment)
        },
        onClick() {
          if (typeof validateForm === 'function') {
            validateForm(true).catch(() => {})
          }
        },
        onAuthorize: this.P_onAuthorize.bind(this),
        onCancel: this.P_onCancel.bind(this),
        onError: this.P_onError.bind(this),
      },
      `#${id}`
    )
    return deferred
  }

  getCaptureToken(cart, cardholderData, paymentType = 'paypal', recaptchaResponse = null, savePaymentMethod = false) {
    if (paymentType === 'none') {
      return Promise.resolve(this.$generateRandomToken('none_'))
    }

    if (!this.flow) {
      return Promise.reject('No active PayPal payment flow detected')
    } else if (this.flow.authorizing) {
      return this.flow.authorize.promise
    }

    return this.$capture(cart, paymentType, recaptchaResponse, savePaymentMethod)
      .then((capture) => {
        this.flow.payment.resolve(capture.data.token)
        this.flow.authorizing = true
        return this.flow.authorize.promise
      })
      .catch((err) => {
        if (this.flow) {
          if (!this.flow.authorizing) {
            this.flow.payment.reject(err)
          }
          this.flow = null
        }
        return Promise.reject(err)
      })
  }

  getSourceToken(paymentMethod, cardholderData, paymentType = 'paypal', recaptchaResponse = null) {
    if (paymentType === 'none') {
      return Promise.resolve(this.$generateRandomToken('none_'))
    }

    if (!this.flow) {
      return Promise.reject('No active PayPal payment flow detected')
    } else if (this.flow.authorizing) {
      return this.flow.authorize.promise
    }

    return this.$tokenize(paymentMethod, paymentType, recaptchaResponse)
      .then((tokenize) => {
        this.flow.payment.resolve(tokenize.data.token)
        this.flow.authorizing = true
        return this.flow.authorize.promise
      })
      .catch((err) => {
        if (this.flow) {
          if (!this.flow.authorizing) {
            this.flow.payment.reject(err)
          }
          this.flow = null
        }
        return Promise.reject(err)
      })
  }

  chargeCaptureToken(cart, data) {
    let payload = {
      provider: this.$name,
      visitor: this.$app.Analytics.$getVisitorId(),
    }
    if (data.billingToken) {
      payload.token = data.billingToken
    } else if (data.paymentToken) {
      payload.token = data.paymentToken
    } else {
      payload.PayerID = data.payerID
      payload.paymentId = data.paymentID
    }
    return this.$http('POST', `carts/${cart.id}/charge`, payload)
  }

  connectSourceToken(paymentMethod, data) {
    let payload = { token: data.billingToken || data.paymentToken }
    return this.$http('POST', `account/payment-methods/${paymentMethod.id}/connect`, payload)
  }

  P_onValidate(actions, id, deferred, validateForm) {
    function validationCheck() {
      // check for container DOM node and clear
      // the interval if no longer present in the DOM
      if (document.getElementById(id)) {
        validateForm(false)
          .then(function () {
            return actions.enable()
          })
          .catch(function () {
            return actions.disable()
          })
      } else {
        clearInterval(deferred.validationCheck)
      }
    }
    deferred.resolve(actions)
    if (typeof validateForm === 'function') {
      actions.disable()
      deferred.validationCheck = setInterval(validationCheck, 1000)
    }
  }

  P_onPayment(data, actions, callback) {
    this.flow = {
      payment: new Deferred(window.paypal.Promise),
      authorize: new Deferred(),
      authorizing: false,
    }
    if (typeof callback === 'function') {
      callback()
    }
    return this.flow.payment
  }

  P_onAuthorize(data) {
    this.flow.authorize.resolve(data)
    this.flow = null
  }

  P_onCancel() {
    this.flow.authorize.reject('PayPal checkout cancelled')
    this.flow = null
  }

  P_onError(err) {
    this.flow.authorize.reject(err)
    this.flow = null
  }
}

export default PayPalCheckoutGateway
