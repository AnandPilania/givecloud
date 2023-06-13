import Deferred from '@core/deferred'
import Gateway from '@core/gateway'

class BraintreeGateway extends Gateway {
  constructor(app) {
    super(app)

    this.$name = 'braintree'
    this.$displayName = 'Braintree (a service of PayPal)'
    this.$currenciesSupportingAch = ['USD']

    this.environment = 'PRODUCTION'
    this.authorization = ''
    this.isAchAllowed = false
    this.isApplePayAllowed = false
    this.isGooglePayAllowed = false
    this.googleMerchantId = null

    this.$client = null
    this.$dataCollector = null
    this.$hostedFields = null
    this.$canMakePayment = null
    this.$applePay = null
    this.$googlePay = null
    this.$googlePayments = null
    this.$parentGooglePayments = null
    this.$usBankAccount = null
  }

  usesHostedPaymentFields() {
    return true
  }

  setConfig({ environment, authorization, ach_allowed, apple_pay_allowed, google_pay_allowed, google_merchant_id }) {
    this.environment = environment
    this.authorization = authorization
    this.isAchAllowed = ach_allowed
    this.isApplePayAllowed = apple_pay_allowed
    this.isGooglePayAllowed = google_pay_allowed
    this.googleMerchantId = google_merchant_id
  }

  async $setupClient() {
    this.$client ||= await this.$app.$window.braintree.client.create({
      authorization: this.authorization,
    })

    return this.$client
  }

  async setupFields(fields, style = {}) {
    if (this.$name !== this.$app.config.gateways.credit_card) {
      return Promise.reject('Braintree not implemented as card gateway.')
    }

    this.$hostedFields = await this.$app.$window.braintree.hostedFields.create({
      client: await this.$setupClient(),
      styles: {
        input: {
          'font-family': '"Courier New", monospace',
          'font-weight': 'normal',
          'font-size': '16px',
        },
        ...style,
      },
      fields: {
        number: {
          container: fields.cardNumber.selector,
          placeholder: fields.cardNumber.placeholder,
        },
        cvv: {
          container: fields.cvv.selector,
          placeholder: fields.cvv.placeholder,
        },
        expirationDate: {
          container: fields.expiryDate.selector,
          placeholder: fields.expiryDate.placeholder,
        },
      },
    })

    const dispatchChangeEvent = (event) => {
      const detail = {
        type: event.emittedBy,
        empty: !!event.fields[event.emittedBy].isEmpty,
        complete: !!event.fields[event.emittedBy].isValid,
        error: null,
      }

      switch (detail.type) {
        case 'number':
          detail.error = detail.complete ? null : 'card_number_invalid'
          break
        case 'expirationDate':
          detail.type = 'exp'
          detail.error = detail.complete ? null : 'card_exp_invalid'
          break
        case 'cvv':
          detail.error = detail.complete ? null : 'card_cvv_invalid'
          break
      }

      this.$app.$window.document.dispatchEvent(new CustomEvent('gc-hosted-field-change', { detail }))
    }

    this.$hostedFields.on('empty', (event) => dispatchChangeEvent(event))
    this.$hostedFields.on('validityChange', (event) => dispatchChangeEvent(event))
    this.$hostedFields.on('blur', (event) => dispatchChangeEvent(event))

    this.$hostedFields.on('cardTypeChange', (event) => {
      this.$app.$window.document.dispatchEvent(new CustomEvent('gc-number-type', { detail: event?.cards?.[0]?.type }))
    })
  }

  async $setupUsBankAccount() {
    if (this.$name !== this.$app.config.gateways.bank_account || !this.isAchAllowed) {
      throw 'Braintree not implemented as bank account gateway.'
    }

    try {
      this.$usBankAccount ||= await this.$app.$window.braintree.usBankAccount.create({
        client: await this.$setupClient(),
      })
    } catch (err) {
      throw this.getOriginalError(err)
    }

    return this.$usBankAccount
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

    await this.$capture(cart, paymentType, recaptchaResponse, savePaymentMethod, {
      ...(cardholderData.wallet_pay && { billing_contact: cardholderData.wallet_pay.billing_contact }),
    })

    try {
      return await this.$tokenizePaymentMethod(paymentType, cardholderData)
    } catch (err) {
      throw this.getOriginalError(err)
    }
  }

  async getSourceToken(paymentMethod, cardholderData, paymentType = 'credit_card', recaptchaResponse = null) {
    if (paymentType === 'none') {
      return this.$generateRandomToken('none_')
    }

    try {
      await this.$tokenize(paymentMethod, paymentType, recaptchaResponse)
      return await this.$tokenizePaymentMethod(paymentType, cardholderData)
    } catch (err) {
      throw this.getOriginalError(err)
    }
  }

  async $tokenizePaymentMethod(paymentType, cardholderData) {
    const token = {
      nonce: null,
      type: 'card',
      device_data: await this.$getDataCollectorDeviceData(),
    }

    if (paymentType === 'credit_card') {
      token.nonce = await this.$tokenizeCreditCard(cardholderData, paymentType)
    }

    if (paymentType === 'bank_account') {
      token.type = 'us_bank_account'
      token.nonce = await this.$tokenizeUsBankAccount(cardholderData, paymentType)
    }

    if (paymentType === 'wallet_pay') {
      token.nonce = cardholderData.wallet_pay?.nonce
    }

    return token
  }

  async $tokenizeCreditCard(cardholderData, paymentType) {
    const { nonce } = await this.$hostedFields.tokenize(this.getCardholderData(cardholderData, paymentType))
    return nonce
  }

  async $tokenizeUsBankAccount(cardholderData, paymentType) {
    const usBankAccount = await this.$setupUsBankAccount()

    try {
      const { nonce } = await usBankAccount.tokenize({
        bankDetails: this.getCardholderData(cardholderData, paymentType),
        mandateText: `By clicking ["Checkout"], I authorize Braintree, a service of PayPal, on behalf of ${this.$app.config.name} (i) to verify my bank account information using bank information and consumer reports and (ii) to debit my bank account.`,
      })

      return nonce
    } catch (err) {
      throw this.getOriginalError(err)
    }
  }

  async $getDataCollectorDeviceData() {
    this.$dataCollector ||= await this.$app.$window.braintree.dataCollector.create({
      client: await this.$setupClient(),
      paypal: false,
    })

    return this.$dataCollector.deviceData
  }

  get $applePaySession() {
    // when executing in an embedded context the apple pay session related needs to
    // be initiated in the context of the parent window or apple pay won't work
    return (this.$app.$parentWindow || this.$app.$window).ApplePaySession
  }

  async canMakePayment() {
    if (this.$canMakePayment !== null) {
      return this.$canMakePayment
    }

    const applePay = Boolean(
      this.isApplePayAllowed && this.$applePaySession?.supportsVersion(3) && this.$applePaySession.canMakePayments()
    )

    // because the apple pay client is asynchronous we need to setup
    // it up ahead of time so when the user clicks apply pay we can create
    // the apple pay session synchronous in the gesture handler
    if (applePay) {
      this.$setupApplePay()
    }

    let googlePay = false

    if (this.isGooglePayAllowed && !this.$applePaySession) {
      await this.$setupGooglePay()

      const { result: isReadyToPay } = await this.googlePayments.isReadyToPay({
        apiVersion: 2,
        apiVersionMinor: 0,
        allowedPaymentMethods: this.$googlePay.createPaymentDataRequest().allowedPaymentMethods,
        existingPaymentMethodRequired: false,
      })

      googlePay = Boolean(isReadyToPay)
    }

    return (this.$canMakePayment = { applePay, googlePay })
  }

  async getWalletPayToken(amount, currencyCode, walletType = null) {
    const canMakePayment = await this.canMakePayment()

    if (canMakePayment.applePay && (walletType === 'applePay' || walletType === null)) {
      return this.$getApplePayToken(amount, currencyCode)
    }

    if (canMakePayment.googlePay && (walletType === 'googlePay' || walletType === null)) {
      return this.$getGooglePayToken(amount, currencyCode)
    }
  }

  async $setupApplePay() {
    this.$applePay ||= await this.$app.$window.braintree.applePay.create({
      client: await this.$setupClient(),
    })

    return this.$applePay
  }

  async $getApplePayToken(amount, currencyCode) {
    const paymentRequest = this.$applePay.createPaymentRequest({
      currencyCode,
      total: {
        label: 'Total',
        amount: parseInt(amount * 100, 10) / 100,
      },
      requiredBillingContactFields: ['postalAddress'],
      requiredShippingContactFields: ['email', 'phone'],
    })

    const session = new this.$applePaySession(3, paymentRequest)
    const walletPayResult = new Deferred()

    session.onvalidatemerchant = async (event) => {
      try {
        const merchantSession = await this.$applePay.performValidation({
          displayName: this.$app.config.name,
          validationURL: event.validationURL,
        })

        session.completeMerchantValidation(merchantSession)
      } catch (err) {
        walletPayResult.reject('Apple Pay failed to load.')
        session.abort()
      }
    }

    session.onpaymentauthorized = async (event) => {
      try {
        const { nonce } = await this.$applePay.tokenize({ token: event.payment.token })

        walletPayResult.resolve({
          type: 'apple_pay',
          nonce,
          billing_contact: {
            first_name: event.payment.billingContact?.givenName || null,
            last_name: event.payment.billingContact?.familyName || null,
            email: event.payment.shippingContact?.emailAddress || null,
            address_line1: event.payment.billingContact?.addressLines?.[0] || null,
            address_line2: event.payment.billingContact?.addressLines?.[1] || null,
            city: event.payment.billingContact?.locality || null,
            state: event.payment.billingContact?.administrativeArea || null,
            postal_code: event.payment.billingContact?.postalCode || null,
            country: event.payment.billingContact?.countryCode || null,
            phone: event.payment.shippingContact?.phoneNumber || null,
          },
        })

        session.completePayment(this.$applePaySession.STATUS_SUCCESS)
      } catch (err) {
        walletPayResult.reject(err)
        session.completePayment(this.$applePaySession.STATUS_FAILURE)
      }
    }

    session.oncancel = () => {
      walletPayResult.reject('PAYMENT_REQUEST_CANCELLED')
    }

    session.begin()
    return walletPayResult.promise
  }

  get googlePayments() {
    return (this.$googlePayments ||= this.$createGooglePaymentsClient(this.$app.$window))
  }

  get parentGooglePayments() {
    if (this.$app.$parentWindow) {
      return (this.$parentGooglePayments ||= this.$createGooglePaymentsClient(this.$app.$parentWindow))
    }

    return this.googlePayments
  }

  $createGooglePaymentsClient(win) {
    return new win.google.payments.api.PaymentsClient({
      environment: this.environment === 'production' ? 'PRODUCTION' : 'TEST',
    })
  }

  async $setupGooglePay() {
    this.$googlePay ||= await this.$app.$window.braintree.googlePayment.create({
      client: await this.$setupClient(),
      googlePayVersion: 2,
      ...(this.googleMerchantId && { googleMerchantId: this.googleMerchantId }),
    })

    return this.$googlePay
  }

  async createGooglePayButton(container, options) {
    await this.canMakePayment()

    if (typeof container === 'string') {
      container = this.$app.$window.document.querySelector(container)
    }

    container.replaceChildren(this.googlePayments.createButton(options))
  }

  async $getGooglePayToken(amount, currencyCode) {
    const paymentDataRequest = this.$googlePay.createPaymentDataRequest({
      emailRequired: true,
      transactionInfo: {
        currencyCode,
        totalPriceLabel: 'Total',
        totalPrice: (parseInt(amount * 100, 10) / 100).toFixed(2),
        totalPriceStatus: 'FINAL',
        checkoutOption: 'COMPLETE_IMMEDIATE_PURCHASE',
      },
    })

    const paymentMethod = paymentDataRequest.allowedPaymentMethods[0]

    paymentMethod.parameters.billingAddressRequired = true
    paymentMethod.parameters.billingAddressParameters = {
      format: 'FULL',
      phoneNumberRequired: true,
    }

    try {
      const paymentData = await this.parentGooglePayments.loadPaymentData(paymentDataRequest)
      const { nonce } = await this.$googlePay.parseResponse(paymentData)

      return {
        type: 'google_pay',
        nonce,
        billing_contact: {
          name: paymentData.paymentMethodData.info?.billingAddress?.name || null,
          email: paymentData.email || null,
          address_line1: paymentData.paymentMethodData.info?.billingAddress?.address1 || null,
          address_line2: paymentData.paymentMethodData.info?.billingAddress?.address2 || null,
          city: paymentData.paymentMethodData.info?.billingAddress?.locality || null,
          state: paymentData.paymentMethodData.info?.billingAddress?.administrativeArea || null,
          postal_code: paymentData.paymentMethodData.info?.billingAddress?.postalCode || null,
          country: paymentData.paymentMethodData.info?.billingAddress?.countryCode || null,
          phone: paymentData.paymentMethodData.info?.billingAddress?.phoneNumber || null,
        },
      }
    } catch (err) {
      if (err?.statusCode === 'CANCELED') {
        throw 'PAYMENT_REQUEST_CANCELLED'
      }

      throw err
    }
  }

  getCardholderData(cardholderData, paymentType) {
    cardholderData = this.$cardholderData(cardholderData)

    let data = {}

    if (paymentType === 'bank_account') {
      cardholderData.requireACH()
      if (!cardholderData.address_line1) {
        throw Error('Billing address is required.')
      }
      if (!cardholderData.address_city) {
        throw Error('Billing City is required.')
      }
      if (!cardholderData.address_state) {
        throw Error('Billing State is required.')
      }
      if (!cardholderData.address_zip) {
        throw Error('Billing Zip is required.')
      }

      if (!cardholderData.address_country) {
        throw Error('Billing Country is required.')
      }

      if (cardholderData.address_country !== 'US') {
        throw Error('Payment through bank account is restricted to US')
      }

      data = {
        accountNumber: cardholderData.account_number || '',
        routingNumber: cardholderData.routing_number || '',
        accountType: cardholderData.account_type || '',
        ownershipType: cardholderData.account_holder_type || '',
        billingAddress: {
          streetAddress: cardholderData.address_line1,
          extendedAddress: cardholderData.address_line2,
          locality: cardholderData.address_city,
          region: cardholderData.address_state,
          postalCode: cardholderData.address_zip,
          country: cardholderData.address_country,
        },
      }

      if (cardholderData.account_holder_type === 'personal') {
        let account_names = (cardholderData.account_holder_name || '').split(' ')
        data.firstName = account_names.shift()
        data.lastName = account_names.join(' ')
      } else {
        data.businessName = cardholderData.account_holder_name || ''
      }

      return data
    }

    if (cardholderData.name) {
      data.cardholderName = cardholderData.name
    }

    if (cardholderData.address_zip) {
      data.billingAddress = {
        streetAddress: cardholderData.address_line1,
        extendedAddress: cardholderData.address_line2,
        locality: cardholderData.address_city,
        region: cardholderData.address_state,
        postalCode: cardholderData.address_zip,
        countryCodeAlpha2: cardholderData.address_country,
      }
    }

    return data
  }

  getOriginalError(error) {
    if (error.details && error.details.originalError) {
      return this.getOriginalError(error.details.originalError)
    }

    return error.message || error[0].message || error
  }
}

export default BraintreeGateway
