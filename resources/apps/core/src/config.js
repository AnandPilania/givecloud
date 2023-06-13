class Config {
  constructor(options) {
    this.site = options.site
    this.account = options.account
    this.account_id = options.account_id
    this.cart_id = options.cart_id
    this.name = options.name
    this.host = options.host
    this.protocol = 'https'
    this.version = 'v1'
    this.context = options.context || 'web'
    this.api_key = null
    this.csrf_token = options.csrf_token
    this.testmode_token = options.testmode_token
    this.currency = options.currency
    this.currencies = options.currencies
    this.money_with_currency = options.money_with_currency || false
    this.locale = options.locale
    this.provider = options.provider
    this.gateways = options.gateways
    this.supported_cardtypes = options.supported_cardtypes
    this.processing_fees = options.processing_fees
    this.account_types = options.account_types
    this.captcha_type = options.captcha_type
    this.requires_captcha = options.requires_captcha
    this.recaptcha_site_key = options.recaptcha_site_key
    this.title_options = options.title_options
    this.referral_sources = options.referral_sources
    this.billing_country_code = options.billing_country_code
    this.shipping_country_code = options.shipping_country_code
    this.force_country = options.force_country
    this.pinned_countries = options.pinned_countries
    this.gift_aid = options.gift_aid

    this.sdk = {
      version: this.version,
      language: 'JS',
    }
  }
}

export default Config
