import Gateway from '@core/gateway'

class NMIGateway extends Gateway {
  constructor(app) {
    super(app)

    this.$name = 'nmi'
    this.$displayName = 'Network Merchants Inc'
    this.$currenciesSupportingAch = ['AUD', 'CAD', 'USD']
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
      .then((capture) => this.$iframePOST(capture.url, cardholderData, cart))
      .then((response) => response.token_id)
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
      .then((source) => this.$iframePOST(source.url, cardholderData))
      .then((response) => response.token_id)
  }

  getCardholderData(cardholderData, paymentType) {
    cardholderData = this.$cardholderData(cardholderData)

    if (paymentType === 'bank_account') {
      cardholderData.requireACH()

      return {
        'billing-account-name': cardholderData.account_holder_name,
        'billing-account-number': cardholderData.account_number,
        'billing-routing-number': cardholderData.routing_number,
        'billing-account-type': cardholderData.account_type,
        'billing-entity-type': cardholderData.account_holder_type,
      }
    }

    cardholderData.requireCreditCard()

    return {
      'billing-cc-number': cardholderData.number,
      'billing-cvv': cardholderData.cvv,
      'billing-cc-exp': cardholderData.exp,
    }
  }

  $iframePOST(url, data, cart) {
    var iframe = document.createElement('iframe')
    iframe.name = 'iframe-' + Date.now()
    iframe.src = 'javascript:false;'
    iframe.style.display = 'none'

    var form = document.createElement('form')
    form.method = 'POST'
    form.action = url
    form.target = iframe.name
    form.style.display = 'none'
    document.body.appendChild(form)

    Object.keys(data).forEach((key) => {
      var input = document.createElement('input')
      input.type = 'hidden'
      input.name = key
      input.value = data[key]
      form.appendChild(input)
    })

    const getCart = () => this.$app.Cart(cart.id).get()

    return new Promise((resolve, reject) => {
      function setupFrame() {
        iframe.removeEventListener('load', setupFrame)
        iframe.addEventListener('load', function () {
          try {
            var doc = this.contentWindow ? this.contentWindow.document : this.contentDocument || this.document
            var root = doc.documentElement || doc.body
            resolve(JSON.parse(root.querySelector('textarea').value))
          } catch (err) {
            if (cart) {
              getCart().then((cart) => {
                if (cart.single_use_token) {
                  resolve({ token_id: cart.single_use_token })
                } else {
                  reject(err)
                }
              })
            } else {
              reject(err)
            }
          }

          document.body.removeChild(form)
          document.body.removeChild(iframe)
        })

        // iOS 12.4 won't submit the form unless
        // we wrap the submit in a setTimeout call
        setTimeout(() => form.submit())
      }

      iframe.addEventListener('load', setupFrame)
      document.body.appendChild(iframe)
    })
  }
}

export default NMIGateway
