import Deferred from '@core/deferred'
import Gateway from '@core/gateway'

class PayPalExpressGateway extends Gateway {
  constructor(app) {
    super(app)

    this.$name = 'paypalexpress'
    this.$displayName = 'PayPal'
    this.$buttonIframe = null
    this.$resetButtonIframe = null

    this.merchantId = ''
    this.environment = 'sandbox'
    this.referenceTransactions = false
  }

  setConfig({ merchant_id, environment, reference_transactions }) {
    this.merchantId = merchant_id
    this.environment = environment || 'sandbox'
    this.referenceTransactions = !!reference_transactions
  }

  canSavePaymentMethods() {
    return this.referenceTransactions
  }

  renderButton({ id, style, onPayment }) {
    if (!this.$app.$window.paypal) {
      throw new Error('Attempt to render PayPal before PayPal has loaded.')
    }
    style.container = id
    this.$app.$window.paypal.checkout.setup(this.merchantId, {
      environment: this.environment,
      buttons: [style],
      click: function (e) {
        e.preventDefault()
        if (typeof onPayment === 'function') {
          onPayment(e)
        }
      },
    })
    return new Deferred()
  }

  setupButton(id, onPayment) {
    const button = this.$app.$window.document.getElementById(id)
    button.setAttribute('data-paypal-click-listener', '')

    this.$setupButtonIframe = () => {
      const contents = `
        <!DOCTYPE html>
        <html>
        <head>
          <title>PayPal Express Checkout Button</title>
          <meta charset="utf-8">
          <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
          <script src="https://www.paypalobjects.com/api/checkout.js" data-version-4 data-merchant-id="${this.merchantId}" data-env="${this.environment}" async></script>
          <style>
            html, body { margin: 0; padding: 0; overflow: hidden; }
            button { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: none; border: none; cursor: pointer; }
          </style>
        </head>
        <body>
        <button type="button" onclick="handleButtonClick()"></button>
        </body>
        </html>
      `

      if (this.$buttonIframe) {
        this.$buttonIframe.remove()
      }

      this.$buttonIframe = this.$app.$window.document.createElement('iframe')
      this.$buttonIframe.setAttribute('allowtransparency', 'true')
      this.$buttonIframe.setAttribute('frameborder', 0)
      button.appendChild(this.$buttonIframe)

      this.$buttonIframe.contentDocument.open('text/html', 'replace')
      this.$buttonIframe.contentDocument.write(contents)
      this.$buttonIframe.contentDocument.close()
      this.$buttonIframe.style.display = 'block'

      // allow PayPal to attempt to open a popup when on mobile
      this.$buttonIframe.contentWindow.navigator.mockUserAgent = window.navigator.userAgent.replace(
        /Android|webOS|iPhone|iPad|iPod|bada|Symbian|Palm|CriOS|BlackBerry|IEMobile|WindowsMobile|Opera Mini/gi,
        'Device'
      )

      this.$buttonIframe.contentWindow.handleButtonClick = onPayment
    }

    this.$setupButtonIframe()
  }

  getCaptureToken(cart, cardholderData, paymentType = 'paypal', recaptchaResponse = null, savePaymentMethod = false) {
    if (paymentType === 'none') {
      return Promise.resolve(this.$generateRandomToken('none_'))
    }

    if (this.$buttonIframe) {
      return this.$getCaptureTokenUsingButtonIframe(cart, recaptchaResponse, savePaymentMethod)
    }

    this.$app.$window.paypal.checkout.initXO()

    return this.$capture(cart, paymentType, recaptchaResponse, savePaymentMethod)
      .then((capture) => {
        return new Promise(() => {
          this.$app.$window.paypal.checkout.startFlow(capture.url)
        })
      })
      .catch((err) => {
        this.$app.$window.paypal.checkout.closeFlow()
        return Promise.reject(err)
      })
  }

  openPayPalCheckoutPopupWindow() {
    this.$buttonIframe.style.display = 'none'
    this.$buttonIframe.contentWindow.paypal.checkout.initXO()
  }

  async $getCaptureTokenUsingButtonIframe(cart, recaptchaResponse, savePaymentMethod) {
    const { url } = await this.$capture(cart, 'paypal', recaptchaResponse, savePaymentMethod)

    return new Promise((resolve, reject) => {
      const onLoad = async () => {
        const { response_text, single_use_token } = await this.$app.Cart(cart.id).get()

        if (single_use_token) {
          const [token_id, payer_id] = single_use_token.split('|')

          resolve({ payer_id, token_id })
        } else {
          reject(response_text || 'PAYPAL_UNKNOWN_ERROR')
        }

        this.$setupButtonIframe()
      }

      this.$buttonIframe.addEventListener('load', onLoad)
      this.$buttonIframe.contentWindow.paypal.checkout.startFlow(url)
    })
  }

  chargeCaptureToken(cart, token) {
    return this.$http('POST', `carts/${cart.id}/charge`, {
      provider: this.$providerName || this.$name,
      token: token.token_id,
      PayerID: token.payer_id,
      visitor: this.$app.Analytics.$getVisitorId(),
    })
  }

  getSourceToken(paymentMethod, cardholderData, paymentType = 'paypal', recaptchaResponse = null) {
    if (paymentType === 'none') {
      return Promise.resolve(this.$generateRandomToken('none_'))
    }

    this.$app.$window.paypal.checkout.initXO()

    return this.$tokenize(paymentMethod, paymentType, recaptchaResponse)
      .then((tokenize) => {
        return new Promise(() => {
          this.$app.$window.paypal.checkout.startFlow(tokenize.url)
        })
      })
      .catch((err) => {
        this.$app.$window.paypal.checkout.closeFlow()
        return Promise.reject(err)
      })
  }
}

export default PayPalExpressGateway
