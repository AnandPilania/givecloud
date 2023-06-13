import Gateway from '@core/gateway'

class PaysafeGateway extends Gateway {
  constructor(app) {
    super(app)

    this.$name = 'paysafe'
    this.$displayName = 'Paysafe'

    this.apiKey = ''
    this.environment = 'TEST'
    this.useThreeDSecureVersion2 = false
    this.cardAccounts = {}
    this.useCheckout = false
    this.preferredMethod = 'Cards'
    this.imageUrl = null
    this.buttonColor = null
    this.preferredMethod = 'Cards'
    this.paysafeInstance = null
  }

  usesHostedPaymentFields() {
    return true
  }

  setConfig({
    api_key,
    environment,
    use_3ds2,
    card_accounts,
    use_checkout,
    preferred_method,
    image_url,
    button_color,
  }) {
    this.apiKey = api_key || this.apiKey
    this.environment = environment || this.environment
    this.useThreeDSecureVersion2 = use_3ds2 || this.useThreeDSecureVersion2
    this.cardAccounts = card_accounts || this.cardAccounts
    this.useCheckout = use_checkout || this.useCheckout
    this.preferredMethod = preferred_method || this.preferredMethod
    this.imageUrl = image_url || this.imageUrl
    this.buttonColor = button_color || this.buttonColor
  }

  startCheckout(cart, data) {
    let options = {
      amount: parseInt((cart.total_price * 100).toFixed(0), 10),
      currency: this.$app.config.currency.code,
      companyName: this.$app.config.name,
      environment: this.environment,
      preferredPaymentMethod: this.preferredMethod,
      hideAmount: true,
    }

    if (this.useThreeDSecureVersion2) {
      options.accounts = {
        CC: this.cardAccounts[cart.currency.code],
      }
      options.threeDS = {
        useThreeDSecureVersion2: true,
        authenticationPurpose: 'RECURRING_TRANSACTION',
      }
    }

    if (this.imageUrl) {
      options.imageUrl = this.imageUrl
    }

    if (this.buttonColor) {
      options.buttonColor = this.buttonColor
    }

    if (data.vault) {
      if (data.vault.holderName) {
        options.holderName = data.vault.holderName
      }

      if (data.vault.billingAddress) {
        options.billingAddress = data.vault.billingAddress
      }
    }

    return new Promise((resolve, reject) => {
      this.$app.$window.paysafe.checkout.setup(
        this.apiKey,
        options,
        (instance, errorResponse, result) => {
          if (errorResponse) {
            reject(errorResponse.displayMessage)
          } else {
            instance.close()
            resolve({
              token: result.token,
              payment_method: result.paymentMethod.toLowerCase(),
            })
          }
        },
        (stage) => {
          if (stage === 'BeforePayment') {
            reject('Checkout cancelled.')
          }
        }
      )
    })
  }

  setupFields(fields, style = {}) {
    let options = {
      environment: this.environment,
      fields,
      style: Object.assign(
        {
          input: {
            'font-family': '"Courier New", monospace',
            'font-weight': 'normal',
            'font-size': '16px',
          },
        },
        style
      ),
    }

    const doc = this.$app.$window.document

    this.$app.$window.paysafe.fields.setup(this.apiKey, options, (instance, error) => {
      if (error) {
        console.error(error)
        return
      }

      this.paysafeInstance = instance

      this.paysafeInstance
        .fields('cardNumber expiryDate cvv')
        .on('FieldValueChange Focus Blur Invalid Valid', function (instance, event) {
          if (event.type === 'Focus') {
            this.classList.add('focus')
          } else if (event.type === 'Blur') {
            this.classList.remove('focus')
          } else if (event.type === 'Invalid') {
            this.classList.add('is-invalid')
          } else if (event.type === 'Valid') {
            this.classList.remove('is-invalid')
          }

          if (['FieldValueChange', 'Invalid', 'Valid'].includes(event.type)) {
            const detail = {
              type: event.target.fieldName,
              empty: !!event.isEmpty,
              complete: event.type === 'Valid',
              error: null,
            }

            switch (detail.type) {
              case 'CardNumber':
                detail.type = 'number'
                detail.error = event.type === 'Invalid' ? 'card_number_invalid' : null
                break
              case 'ExpiryDate':
                detail.type = 'exp'
                detail.error = event.type === 'Invalid' ? 'card_exp_invalid' : null
                break
              case 'Cvv':
                detail.type = 'cvv'
                detail.error = event.type === 'Invalid' ? 'card_cvv_invalid' : null
                break
            }

            doc.dispatchEvent(new CustomEvent('gc-hosted-field-change', { detail }))
          }
        })

      instance.cardBrandRecognition(function (instance, event) {
        let brand = ''
        // prettier-ignore
        switch (event.data.cardBrand) {
          case 'American Express': brand = 'american-express'; break
          case 'Diners Club': brand = 'diners-club'; break
          case 'Discover': brand = 'discover'; break
          case 'JCB': brand = 'jcb'; break
          case 'Maestro': brand = 'maestro'; break
          case 'MasterCard': brand = 'master-card'; break
          case 'Visa': brand = 'visa'; break
        }

        doc.dispatchEvent(new CustomEvent('gc-number-type', { detail: brand }))
      })
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
      var data = this.getCardholderData(cardholderData, paymentType)
    } catch (err) {
      return Promise.reject(err)
    }

    return this.$capture(cart, paymentType, recaptchaResponse, savePaymentMethod).then(() => {
      if (this.useCheckout && cart.total_price > 0) {
        return this.startCheckout(cart, data)
      }

      return new Promise((resolve, reject) => {
        if (this.useThreeDSecureVersion2) {
          data.threeDS = {
            useThreeDSecureVersion2: true,
            authenticationPurpose: 'PAYMENT_TRANSACTION',
            accountId: this.cardAccounts[cart.currency.code],
            amount: cart.total_price_in_subunits,
            currency: cart.currency.code,
          }
        }

        this.paysafeInstance.tokenize(data, function (instance, errorResponse, result) {
          if (errorResponse) {
            reject(errorResponse.displayMessage)
          } else {
            resolve(result.token)
          }
        })
      })
    })
  }

  getSourceToken(paymentMethod, cardholderData, paymentType = 'credit_card', recaptchaResponse = null) {
    if (paymentType === 'none') {
      return Promise.resolve(this.$generateRandomToken('none_'))
    }

    try {
      var data = this.getCardholderData(cardholderData, paymentType)
    } catch (err) {
      return Promise.reject(err)
    }

    return this.$tokenize(paymentMethod, paymentType, recaptchaResponse).then(() => {
      return new Promise((resolve, reject) => {
        if (this.useThreeDSecureVersion2) {
          data.threeDS = {
            useThreeDSecureVersion2: true,
            authenticationPurpose: 'ADD_CARD',
            accountId: this.cardAccounts[this.$app.config.currency.code],
            amount: 0,
            currency: this.$app.config.currency.code,
          }
        }

        this.paysafeInstance.tokenize(data, function (instance, errorResponse, result) {
          if (errorResponse) {
            reject(errorResponse.displayMessage)
          } else {
            resolve(result.token)
          }
        })
      })
    })
  }

  getCardholderData(cardholderData, paymentType, data = {}) {
    cardholderData = this.$cardholderData(cardholderData)

    if (cardholderData.name) {
      data.vault = data.vault || {}
      data.vault.holderName = cardholderData.name
    }

    if (cardholderData.address_zip && cardholderData.address_country) {
      data.vault = data.vault || {}
      data.vault.billingAddress = {
        street: cardholderData.address_line1,
        street2: cardholderData.address_line2,
        city: cardholderData.address_city,
        state: cardholderData.address_state,
        zip: cardholderData.address_zip,
        country: cardholderData.address_country,
      }
    }

    return data
  }
}

export default PaysafeGateway
