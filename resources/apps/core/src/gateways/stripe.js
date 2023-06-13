import Gateway from '@core/gateway'
import { objectFilterNulls } from '@core/utils'

const stripeError = (error) => {
  const isDisclosable = error?.type === 'card_error' || error?.type === 'validation_error'

  throw {
    error,
    disclosableMessage: isDisclosable ? error?.message : null,
  }
}

class StripeGateway extends Gateway {
  constructor(app) {
    super(app)

    this.$name = 'stripe'
    this.$displayName = 'Stripe'

    this.publishableKey = null
    this.stripeAccount = null
    this.usingStripeV2 = false

    this.$stripe = null
    this.$parentStripe = null

    this.elements = {
      cardNumber: null,
      cardExpiry: null,
      cardCvc: null,
    }
  }

  setConfig({ publishableKey, stripeAccount, usingStripeV2 }) {
    this.publishableKey = publishableKey
    this.stripeAccount = stripeAccount
    this.usingStripeV2 = usingStripeV2
  }

  get stripe() {
    return (this.$stripe ||= this.$createStripeClient(this.$app.$window))
  }

  get parentStripe() {
    if (this.$app.$parentWindow) {
      return (this.$parentStripe ||= this.$createStripeClient(this.$app.$parentWindow))
    }

    return this.stripe
  }

  $createStripeClient(win) {
    const options = {
      ...(this.stripeAccount && { stripeAccount: this.stripeAccount }),
    }

    return win.Stripe(this.publishableKey, options)
  }

  usesHostedPaymentFields() {
    return this.usingStripeV2 === false
  }

  setupFields(fields, style = {}, opts = {}, config = {}) {
    if (this.usingStripeV2) {
      return
    }

    // cleanup any previously created elements
    // before attempting to create and mount new ones
    Object.keys(this.elements).forEach((key) => {
      this.elements[key]?.destroy()
      this.elements[key] = null
    })

    const elements = this.stripe.elements(opts)

    const options = {
      classes: {
        complete: 'valid',
        invalid: 'is-invalid',
        focus: 'focus',
      },
      style: {
        base: {
          fontFamily: '"Courier New", monospace',
          fontWeight: 'normal',
          fontSize: '16px',
          lineHeight: '1.5',
        },
        invalid: {
          backgroundColor: '#fff2f2',
          color: '#710000',
          '::placeholder': {
            color: '#c57878',
          },
        },
        ...style,
      },
    }

    if (fields.cardNumber) {
      this.elements.cardNumber = elements.create('cardNumber', {
        placeholder: fields.cardNumber.placeholder,
        ...options,
      })

      this.elements.cardNumber.on('change', (event) => {
        this.$handleElementOnChange(event, 'number', fields)
        this.$dispatchNumberTypeEvent(event)

        if (config.followFocus && event.complete) {
          this.elements.cardExpiry?.focus()
        }
      })

      this.elements.cardNumber.mount(fields.cardNumber.selector)
    }

    if (fields.cardExpiry) {
      this.elements.cardExpiry = elements.create('cardExpiry', {
        placeholder: fields.cardExpiry.placeholder,
        ...options,
      })

      this.elements.cardExpiry.on('change', (event) => {
        this.$handleElementOnChange(event, 'exp', fields)

        if (config.followFocus && event.empty) {
          this.elements.cardNumber?.focus()
        }

        if (config.followFocus && event.complete) {
          this.elements.cardCvc?.focus()
        }
      })

      this.elements.cardExpiry.mount(fields.cardExpiry.selector)
    }

    if (fields.cardCvc) {
      this.elements.cardCvc = elements.create('cardCvc', {
        placeholder: fields.cardCvc.placeholder,
        ...options,
      })

      this.elements.cardCvc.on('change', (event) => {
        this.$handleElementOnChange(event, 'cvv', fields)

        if (config.followFocus && event.empty) {
          this.elements.cardExpiry?.focus()
        }
      })

      this.elements.cardCvc.mount(fields.cardCvc.selector)
    }
  }

  $handleElementOnChange(event, shortName, fields) {
    const type = event.elementType
    const parent = this.elements[type]._parent.closest(fields[type].container)

    if (parent) {
      parent.classList[event.error ? 'add' : 'remove']('has-errors', 'has-stripe-errors')
      parent.classList[event.complete ? 'add' : 'remove'](`valid-${shortName}`)
    }

    const detail = {
      type: shortName,
      empty: !!event.empty,
      complete: !!event.complete,
      error: event?.error?.message,
    }

    this.$app.$window.document.dispatchEvent(new CustomEvent('gc-hosted-field-change', { detail }))
  }

  $dispatchNumberTypeEvent(event) {
    let brand = event.brand

    // prettier-ignore
    switch (event.brand) {
      case 'amex': brand = 'american-express'; break
      case 'diners': brand = 'diners-club'; break
      case 'mastercard': brand = 'master-card'; break
    }

    this.$app.$window.document.dispatchEvent(new CustomEvent('gc-number-type', { detail: brand }))
  }

  async getCaptureToken(
    cart,
    cardholderData,
    paymentType = 'credit_card',
    recaptchaResponse = null,
    savePaymentMethod = false
  ) {
    if (paymentType === 'none') {
      return this.$generateRandomToken('none_')
    }

    if (this.usingStripeV2) {
      await this.$capture(cart, paymentType, recaptchaResponse, savePaymentMethod, { using_stripe_v2: true })
      return this.$tokenizeCard(cardholderData)
    }

    let paymentMethod = null

    if (paymentType === 'credit_card') {
      paymentMethod = await this.$createPaymentMethod(cart.billing_address, cardholderData)
    }

    if (paymentType === 'wallet_pay') {
      paymentMethod = cardholderData.wallet_pay
    }

    const result = await this.$capture(cart, paymentType, recaptchaResponse, savePaymentMethod, {
      ...(paymentMethod && { payment_method: paymentMethod }),
    })

    if (result.object === 'payment_intent') {
      return await this.$confirmCardPayment(result, cart.billing_address, cardholderData, savePaymentMethod)
    }

    return await this.$confirmCardSetup(result, cart.billing_address, cardholderData)
  }

  async getSourceToken(paymentMethod, cardholderData, paymentType = 'credit_card', recaptchaResponse = null) {
    if (paymentType === 'none') {
      return this.$generateRandomToken('none_')
    }

    if (this.usingStripeV2) {
      await this.$tokenize(paymentMethod, paymentType, recaptchaResponse, { using_stripe_v2: true })
      return this.$tokenizeCard(cardholderData)
    }

    const result = await this.$tokenize(paymentMethod, paymentType, recaptchaResponse)
    return await this.$confirmCardSetup(result, paymentMethod.billing_address, cardholderData)
  }

  async canMakePayment() {
    if (this.usingStripeV2) {
      return false
    }

    return this.$createPaymentRequest().canMakePayment()
  }

  async getWalletPayToken(amount, currencyCode) {
    if (this.usingStripeV2) {
      throw `Stripe.js V2 doesn't support wallet pay.`
    }

    const paymentRequest = this.$createPaymentRequest({
      currency: currencyCode.toLowerCase(),
      total: {
        label: 'Total',
        amount: parseInt(amount * 100, 10),
      },
    })

    return new Promise((resolve, reject) => {
      let tokenEventHasNotFired = true

      paymentRequest.on('paymentmethod', (event) => {
        tokenEventHasNotFired = false
        resolve(event.paymentMethod.id)
        event.complete('success')
      })

      paymentRequest.on('cancel', () => {
        // In some browsers the payment interface may be dismissed even after they authorize
        // the payment. Which means a cancel event might be fired after receiving a payment method.
        if (tokenEventHasNotFired) {
          reject('PAYMENT_REQUEST_CANCELLED')
        }
      })

      paymentRequest.canMakePayment().then((available) => {
        if (available) {
          paymentRequest.show()
        } else {
          reject('Payment Request API not currently available')
        }
      })
    })
  }

  async $createPaymentMethod(address, cardholderData) {
    const { error, paymentMethod } = await this.stripe.createPaymentMethod({
      type: 'card',
      card: this.elements.cardNumber,
      billing_details: this.$getBillingDetails(address, cardholderData),
    })

    if (error) {
      throw stripeError(error)
    }

    return paymentMethod.id
  }

  $tokenizeCard(cardholderData) {
    this.$app.$window.Stripe.setPublishableKey(this.publishableKey)

    return new Promise((resolve, reject) => {
      const data = this.$getCardBillingDetails(cardholderData)

      this.$app.$window.Stripe.card.createToken(data, (status, res) => {
        if (res.error) {
          reject(res.error)
        } else {
          resolve(res.id)
        }
      })
    })
  }

  async $confirmCardPayment(paymentIntent, address, cardholderData, savePaymentMethod) {
    const params = {
      payment_method: paymentIntent.payment_method || cardholderData.wallet_pay || null,
      setup_future_usage: savePaymentMethod ? 'off_session' : null,
    }

    if (params.payment_method === null) {
      params.payment_method = {
        card: this.elements.cardNumber,
        billing_details: this.$getBillingDetails(address, cardholderData),
      }
    }

    const { error } = await this.stripe.confirmCardPayment(paymentIntent.client_secret, params)

    // ensure card errors are not thrown these will be logged
    // and handled in the backend during the getChargeToken request
    if (error && error.type !== 'card_error') {
      throw stripeError(error)
    }

    return paymentIntent.id
  }

  async $confirmCardSetup(setupIntent, address, cardholderData) {
    const params = {
      payment_method: setupIntent.payment_method || cardholderData.wallet_pay || null,
    }

    if (params.payment_method === null) {
      params.payment_method = {
        card: this.elements.cardNumber,
        billing_details: this.$getBillingDetails(address, cardholderData),
      }
    }

    const { error } = await this.stripe.confirmCardSetup(setupIntent.client_secret, params)

    if (error) {
      throw stripeError(error)
    }

    return setupIntent.id
  }

  $createPaymentRequest(options = {}) {
    return this.parentStripe.paymentRequest({
      country: this.$app.config.billing_country_code,
      currency: this.$app.config.currency.code.toLowerCase(),
      total: {
        label: 'Total',
        amount: 0,
      },
      requestPayerName: true,
      requestPayerEmail: true,
      requestPayerPhone: true,
      ...options,
    })
  }

  $getBillingDetails(address, cardholderData) {
    return objectFilterNulls({
      name: cardholderData.name,
      email: address?.email,
      address: objectFilterNulls({
        line1: cardholderData.address_line1,
        line2: cardholderData.address_line2,
        city: cardholderData.address_city,
        state: cardholderData.address_state,
        postal_code: cardholderData.address_zip,
        country: cardholderData.address_country,
      }),
    })
  }

  $getCardBillingDetails(data) {
    const cardholderData = this.$cardholderData(data)
    cardholderData.requireCreditCard()

    return {
      name: cardholderData.name,
      address_line1: cardholderData.address_line1,
      address_line2: cardholderData.address_line2,
      address_city: cardholderData.address_city,
      address_state: cardholderData.address_state,
      address_zip: cardholderData.address_zip,
      address_country: cardholderData.address_country,
      number: cardholderData.number,
      cvc: cardholderData.cvv,
      exp_month: cardholderData.exp_month,
      exp_year: cardholderData.exp_year,
    }
  }
}

export default StripeGateway
