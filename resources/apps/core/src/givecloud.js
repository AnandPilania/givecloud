import 'es6-promise/auto'
import 'custom-event-polyfill'

import axios from 'axios'
import Config from './config'
import AccountEndpoint from './endpoints/account'
import AnalyticsEndpoint from './endpoints/analytics'
import CartEndpoint from './endpoints/cart'
import DccService from './services/dcc'
import CsrfTokenEndpoint from './endpoints/csrf-token'
import PledgeCampaignsEndpoint from './endpoints/pledge-campaigns'
import ProductsEndpoint from './endpoints/products'
import ServicesEndpoint from './endpoints/services'
import CardholderData from './cardholder-data'
import FundraisingPagesEndpoint from './endpoints/fundraising-pages'
import PeerToPeerCampaignsEndpoint from './endpoints/peer-to-peer-campaigns'
import AuthorizeNetGateway from './gateways/authorizenet'
import BraintreeGateway from './gateways/braintree'
import CaymanGatewayGateway from './gateways/caymangateway'
import GivecloudTestGateway from './gateways/givecloudtest'
import GoCardlessTestGateway from './gateways/gocardless'
import NMIGateway from './gateways/nmi'
import PaymentMethodGateway from './gateways/paymentmethod'
import PaysafeGateway from './gateways/paysafe'
import PayPalCheckoutGateway from './gateways/paypalcheckout'
import PayPalExpressGateway from './gateways/paypalexpress'
import SafeSaveGateway from './gateways/safesave'
import StripeGateway from './gateways/stripe'
import VancoGateway from './gateways/vanco'

const cartCreate = function (data = {}) {
  const endpoint = new CartEndpoint(this)

  return endpoint.create(data).then((cart) => {
    this.$carts.set(cart.id, endpoint)
    return Promise.resolve(cart)
  })
}

const cartOneClickCheckout = function (data, cart, paymentType, requireBillingAddress) {
  const endpoint = cart ? this.Cart(cart.id) : new CartEndpoint(this)

  return endpoint.oneClickCheckout(data, paymentType, requireBillingAddress).then((cart) => {
    this.$carts.set(cart.id, endpoint)
    return Promise.resolve(cart)
  })
}

const gatewayGetDefaultPaymentType = function (cart) {
  if (cart && cart.requires_ach) return 'bank_account'
  if (cart && cart.payment_type) return cart.payment_type
  if (cart && cart.account && cart.account.payment_methods.length) return 'payment_method'
  if (this.config.gateways.credit_card) return 'credit_card'
  if (this.config.gateways.bank_account) return 'bank_account'
  if (this.config.gateways.paypal) return 'paypal'
}

class Givecloud {
  constructor(config) {
    this.config = config

    this.Account = new AccountEndpoint(this)
    this.Analytics = new AnalyticsEndpoint(this)
    this.CsrfToken = new CsrfTokenEndpoint(this)
    this.Dcc = new DccService(this)
    this.FundraisingPages = new FundraisingPagesEndpoint(this)
    this.PeerToPeerCampaigns = new PeerToPeerCampaignsEndpoint(this)
    this.PledgeCampaigns = new PledgeCampaignsEndpoint(this)
    this.Products = new ProductsEndpoint(this)
    this.Services = new ServicesEndpoint(this)

    this.CardholderData = CardholderData

    this.$window = window
    this.$parentWindow = null

    this.$carts = new Map()
    this.$gateways = new Map()

    this.Cart.create = cartCreate.bind(this)
    this.Cart.oneClickCheckout = cartOneClickCheckout.bind(this)
    this.Gateway.getDefaultPaymentType = gatewayGetDefaultPaymentType.bind(this)
  }

  setConfig(config) {
    this.config = new Config(config)
    this.Account.$account = this.config.account

    this.setCsrfToken(config.csrf_token)
  }

  setApiKey(apiKey) {
    this.config.api_key = apiKey
  }

  setContext(context) {
    this.config.context = context || 'web'
  }

  setCsrfToken(token) {
    this.config.csrf_token = token
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token
  }

  Cart(id = this.config.cart_id) {
    if (!this.$carts.has(id)) {
      this.$carts.set(id, new CartEndpoint(this, id))
    }

    return this.$carts.get(id)
  }

  Gateway(provider = this.config.provider) {
    if (!this.$gateways.has(provider)) {
      // prettier-ignore
      switch (provider) {
        case 'authorizenet': this.$gateways.set(provider, new AuthorizeNetGateway(this)); break
        case 'braintree': this.$gateways.set(provider, new BraintreeGateway(this)); break
        case 'caymangateway': this.$gateways.set(provider, new CaymanGatewayGateway(this)); break
        case 'givecloudtest': this.$gateways.set(provider, new GivecloudTestGateway(this)); break
        case 'gocardless': this.$gateways.set(provider, new GoCardlessTestGateway(this)); break
        case 'nmi': this.$gateways.set(provider, new NMIGateway(this)); break
        case 'paymentmethod': this.$gateways.set(provider, new PaymentMethodGateway(this)); break
        case 'paypalcheckout': this.$gateways.set(provider, new PayPalCheckoutGateway(this)); break
        case 'paypalexpress': this.$gateways.set(provider, new PayPalExpressGateway(this)); break
        case 'paysafe': this.$gateways.set(provider, new PaysafeGateway(this)); break
        case 'safesave': this.$gateways.set(provider, new SafeSaveGateway(this)); break
        case 'stripe': this.$gateways.set(provider, new StripeGateway(this)); break
        case 'vanco': this.$gateways.set(provider, new VancoGateway(this)); break
      }
    }

    return this.$gateways.get(provider)
  }

  GatewayAlias(provider, alias) {
    this.$gateways.set(provider, this.Gateway(alias))
  }

  PaymentTypeGateway(paymentType) {
    if (paymentType === 'payment_method') {
      return this.Gateway('paymentmethod')
    } else {
      return this.Gateway(this.config.gateways[paymentType])
    }
  }
}

export default Givecloud
