import validCard from 'card-validator'

function toNullableString(value) {
  if (typeof value === 'undefined' || value === null) {
    return null
  }

  return String(value) || null
}

export default class CardholderData {
  constructor(data = {}, supported_cardtypes) {
    this.data = {
      name: null,
      address_line1: null,
      address_line2: null,
      address_city: null,
      address_state: null,
      address_zip: null,
      address_country: null,
      number: null,
      brand: null,
      cvv: null,
      exp_month: null,
      exp_year: null,
      country: null,
      currency: null,
      transit_number: null,
      institution_number: null,
      routing_number: null,
      account_number: null,
      account_type: null,
      account_holder_name: null,
      account_holder_type: null,
      wallet_pay: null,
    }

    this.supported_cardtypes = supported_cardtypes
    this.fill(data)
  }

  /**
   * Create instance for use with ACH.
   *
   * @param object data
   * @param array supported_cardtypes
   * @return CardholderData
   */
  static forACH(data, supported_cardtypes) {
    return new CardholderData(data, supported_cardtypes).requireACH()
  }

  /**
   * Create instance for use with credit card.
   *
   * @param object data
   * @param array supported_cardtypes
   * @return CardholderData
   */
  static forCreditCard(data, supported_cardtypes) {
    return new CardholderData(data, supported_cardtypes).requireCreditCard()
  }

  /**
   * Get credit card type.
   *
   * @param string number
   * @return string|null
   */
  static getNumberType(number) {
    const valid = validCard.number(number)

    return (valid.card && valid.card.type) || null
  }

  /**
   * Validates a credit card number.
   *
   * @param string value
   * @return boolean
   */
  static validNumber(value) {
    return validCard.number(value).isValid
  }

  /**
   * Validates an expiration date.
   *
   * @param string value
   * @return boolean
   */
  static validExpirationDate(value) {
    return validCard.expirationDate(value).isValid
  }

  /**
   * Validates a cvv.
   *
   * @param string value
   * @return boolean
   */
  static validCvv(value, number_type) {
    var length = 3

    if (number_type === 'american-express') {
      length = 4
    }

    return validCard.cvv(value, length).isValid
  }

  /**
   * Fill cardholder data.
   *
   * @param object
   */
  fill(data) {
    const properties = Object.keys(this.data)

    if (data instanceof CardholderData) {
      data = data.data
    }

    Object.keys(data).forEach((key) => {
      if (properties.includes(key) || key === 'exp') {
        this[key] = data[key] || this[key]
      }
    })
  }

  /**
   * Get name.
   *
   * @return string
   */
  get name() {
    return this.data.name
  }

  /**
   * Set and validate name.
   *
   * @param string value
   */
  set name(value) {
    this.data.name = toNullableString(value)
  }

  /**
   * Get address line 1.
   *
   * @return string
   */
  get address_line1() {
    return this.data.address_line1
  }

  /**
   * Set and validate address line 1.
   *
   * @param string value
   */
  set address_line1(value) {
    this.data.address_line1 = toNullableString(value)
  }

  /**
   * Get address line 2.
   *
   * @return string
   */
  get address_line2() {
    return this.data.address_line2
  }

  /**
   * Set and validate address line 2.
   *
   * @param string value
   */
  set address_line2(value) {
    this.data.address_line2 = toNullableString(value)
  }

  /**
   * Get city.
   *
   * @return string
   */
  get address_city() {
    return this.data.address_city
  }

  /**
   * Set and validate city.
   *
   * @param string value
   */
  set address_city(value) {
    this.data.address_city = toNullableString(value)
  }

  /**
   * Get state.
   *
   * @return string
   */
  get address_state() {
    return this.data.address_state
  }

  /**
   * Set and validate state.
   *
   * @param string value
   */
  set address_state(value) {
    this.data.address_state = toNullableString(value)
  }

  /**
   * Get zip.
   *
   * @return string
   */
  get address_zip() {
    return this.data.address_zip
  }

  /**
   * Set and validate zip.
   *
   * @param string value
   */
  set address_zip(value) {
    this.data.address_zip = toNullableString(value)
  }

  /**
   * Get country.
   *
   * @return string
   */
  get address_country() {
    return this.data.address_country
  }

  /**
   * Set and validate country.
   *
   * @param string value
   */
  set address_country(value) {
    this.data.address_country = toNullableString(value)
  }

  /**
   * Get card number.
   *
   * @return string
   */
  get number() {
    return this.data.number
  }

  /**
   * Set and validate card number.
   *
   * @param string value
   */
  set number(value) {
    value = toNullableString(value)

    if (value === null) {
      this.data.number = null
      return
    }

    const verification = validCard.number(value)

    if (!verification.isValid) {
      throw Error('Credit card number is invalid.')
    }

    if (this.supported_cardtypes && this.supported_cardtypes.indexOf(verification.card.type) === -1) {
      throw Error(`${verification.card.niceType} cards are not supported.`)
    }

    this.data.number = value
    this.data.brand = verification.card.niceType

    // revalidate the CVV
    if (this.data.cvv) {
      this.cvv = this.data.cvv
    }
  }

  /**
   * Get card brand value.
   *
   * @return string
   */
  get brand() {
    return this.data.brand
  }

  /**
   * Get card brand value.
   *
   * @param string value
   */
  set brand(value) {
    this.data.brand = toNullableString(value)
  }

  /**
   * Get card validation value.
   *
   * @return string
   */
  get cvv() {
    return this.data.cvv
  }

  /**
   * Set and validate card validation value.
   *
   * @param string value
   */
  set cvv(value) {
    value = toNullableString(value)

    if (value === null) {
      this.data.cvv = null
      return
    }

    const numberValidation = validCard.number(this.data.number)

    let length = 3
    if (numberValidation && numberValidation.card) {
      length = numberValidation.card.code.size
    }

    if (!validCard.cvv(value, length).isValid) {
      throw Error('CVV must be 4 digits for American Express and 3 digits for other card types.')
    }

    this.data.cvv = value
  }

  /**
   * Get expiration date.
   *
   * @return string
   */
  get exp() {
    if (!(this.exp_month || this.exp_year)) {
      return ''
    }

    if (!this.exp_month) {
      throw Error('Expiration month is required.')
    }

    if (!this.exp_year) {
      throw Error('Expiration year is required.')
    }

    return this.exp_month + this.exp_year
  }

  /**
   * Set and validate expiration date.
   *
   * @param string value
   */
  set exp(value) {
    value = toNullableString(value)

    if (value === null) {
      this.exp_month = null
      this.exp_year = null
      return
    }

    value = validCard.expirationDate(value)

    if (!value.isValid) {
      throw Error('Expiration date is invalid.')
    }

    this.exp_month = value.month
    this.exp_year = value.year
  }

  /**
   * Get expiration month.
   *
   * @return string
   */
  get exp_month() {
    return this.data.exp_month
  }

  /**
   * Set and validate expiration month.
   *
   * @param string value
   */
  set exp_month(value) {
    value = toNullableString(value)

    if (value === null) {
      this.data.exp_month = null
      return
    }

    if (!validCard.expirationMonth(value).isValid) {
      throw Error('Expiration month is invalid.')
    }

    // store as 2 digit month
    this.data.exp_month = ('0' + value).substr(-2)
  }

  /**
   * Get expiration year.
   *
   * @return string
   */
  get exp_year() {
    return this.data.exp_year
  }

  /**
   * Set and validate expiration year.
   *
   * @param string value
   */
  set exp_year(value) {
    value = toNullableString(value)

    if (value === null) {
      this.data.exp_year = null
      return
    }

    if (!validCard.expirationYear(value).isValid) {
      throw Error('Expiration year is invalid.')
    }

    // store as 2 digit year
    this.data.exp_year = value.replace(/^\d\d(\d\d)/, '$1')
  }

  /**
   * Get transit number.
   *
   * @return string
   */
  get transit_number() {
    return this.data.transit_number
  }

  /**
   * Set and validate transit number.
   *
   * @param string value
   */
  set transit_number(value) {
    value = toNullableString(value)

    if (value === null) {
      this.data.transit_number = null
      return
    }

    this.data.transit_number = value
  }

  /**
   * Get institution number.
   *
   * @return string
   */
  get institution_number() {
    return this.data.institution_number
  }

  /**
   * Set and validate institution number.
   *
   * @param string value
   */
  set institution_number(value) {
    value = toNullableString(value)

    if (value === null) {
      this.data.institution_number = null
      return
    }

    this.data.institution_number = value
  }

  /**
   * Get routing number.
   *
   * @return string
   */
  get routing_number() {
    if (this.currency === 'CAD') {
      return this.institution_number + this.transit_number
    }

    return this.data.routing_number
  }

  /**
   * Set and validate routing number.
   *
   * @param string value
   */
  set routing_number(value) {
    value = toNullableString(value)

    if (value === null) {
      this.data.routing_number = null
      return
    }

    this.data.routing_number = value
  }

  /**
   * Get account number.
   *
   * @return string
   */
  get account_number() {
    return this.data.account_number
  }

  /**
   * Set and validate account number.
   *
   * @param string value
   */
  set account_number(value) {
    value = toNullableString(value)

    if (value === null) {
      this.data.account_number = null
      return
    }

    this.data.account_number = value
  }

  /**
   * Get account type.
   *
   * @return string
   */
  get account_type() {
    return this.data.account_type
  }

  /**
   * Set and validate account type.
   *
   * @param string value
   */
  set account_type(value) {
    value = toNullableString(value)

    if (value && !(value === 'checking' || value === 'savings')) {
      throw Error('Account type is invalid.')
    }

    this.data.account_type = value
  }

  /**
   * Get account holder name.
   *
   * @return string
   */
  get account_holder_name() {
    return this.data.account_holder_name || this.name
  }

  /**
   * Set and validate account holder name.
   *
   * @param string value
   */
  set account_holder_name(value) {
    this.data.account_holder_name = toNullableString(value)
  }

  /**
   * Get account_holder_type.
   *
   * @return string
   */
  get account_holder_type() {
    return this.data.account_holder_type
  }

  /**
   * Set and validate account_holder_type.
   *
   * @param string value
   */
  set account_holder_type(value) {
    value = toNullableString(value)

    if (value && !(value === 'personal' || value === 'business')) {
      throw Error('Account holder type is invalid.')
    }

    this.data.account_holder_type = value
  }

  /**
   * Get wallet pay data.
   *
   * @return object|null
   */
  get wallet_pay() {
    return this.data.wallet_pay
  }

  /**
   * Set wallet pay data.
   *
   * @param object|null value
   */
  set wallet_pay(value) {
    this.data.wallet_pay = value
  }

  /**
   * Check for ACH data.
   *
   * @return boolean
   */
  isACH() {
    return (
      this.routing_number ||
      this.account_number ||
      this.account_type ||
      this.account_holder_name ||
      this.account_holder_type
    )
  }

  /**
   * Check for credit card data.
   *
   * @return boolean
   */
  isCreditCard() {
    return !this.isACH()
  }

  /**
   * Check for required ACH data.
   *
   * @return this
   */
  requireACH() {
    if (this.currency === 'CAD') {
      if (!this.transit_number) {
        throw Error('Transit number is required.')
      }

      if (!this.institution_number) {
        throw Error('Institution number is required.')
      }
    } else {
      if (!this.routing_number) {
        throw Error('Routing number is required.')
      }
    }

    if (!this.account_number) {
      throw Error('Account number is required.')
    }

    if (!this.account_holder_type) {
      throw Error('Account holder type is required.')
    }

    return this
  }

  /**
   * Check for required credit card data.
   *
   * @return this
   */
  requireCreditCard() {
    if (!this.number) {
      throw Error('Credit card number is required.')
    }

    if (!this.exp) {
      throw Error('Expiration date is required.')
    }

    return this
  }
}
