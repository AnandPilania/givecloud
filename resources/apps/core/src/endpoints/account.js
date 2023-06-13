import Endpoint from '@core/endpoint'
import EventEmitter from 'wolfy87-eventemitter'
import OrdersEndpoint from './account/orders'
import PaymentMethodsEndpoint from './account/payment-methods'
import PeerToPeerCampaignsEndpoint from './account/peer-to-peer-campaigns'
import SponsorshipsEndpoint from './account/sponsorships'
import SubscriptionsEndpoint from './account/subscriptions'

class AccountEndpoint extends Endpoint {
  constructor(app) {
    super(app)

    this.$account = null
    this.$events = new EventEmitter()

    this.Orders = new OrdersEndpoint(app)
    this.PaymentMethods = new PaymentMethodsEndpoint(app)
    this.PeerToPeerCampaigns = new PeerToPeerCampaignsEndpoint(app)
    this.Sponsorships = new SponsorshipsEndpoint(app)
    this.Subscriptions = new SubscriptionsEndpoint(app)
  }

  subscribe(fn) {
    this.$events.on('change', fn)
  }

  get(forceRefresh = false) {
    if (this.$account && !forceRefresh) {
      return Promise.resolve({ account: this.$account })
    }
    return this.$http('GET', `account`).then((data) => {
      this.$account = data.account
      this.$events.emitEvent('change', [this.$account])
      return Promise.resolve(data)
    })
  }

  update(data) {
    return this.$http('PATCH', `account`, data).then((data) => {
      this.$account = data.account
      this.$events.emitEvent('change', [this.$account])
      return Promise.resolve(data)
    })
  }

  changePassword(data) {
    return this.$http('POST', `account/change-password`, data)
  }

  login(data, password, remember_me = false) {
    if (typeof data === 'string') {
      data = { email: data, password, remember_me }
    }
    return this.$http('POST', `account/login`, data).then((data) => {
      this.$account = data.account
      this.$app.config.account_id = this.$account.id
      this.$events.emitEvent('change', [this.$account])
      return Promise.resolve(data)
    })
  }

  logout() {
    return this.$http('GET', `account/logout`).then((data) => {
      this.$account = null
      this.$app.config.account_id = null
      this.$events.emitEvent('change', [null])
      return Promise.resolve(data)
    })
  }

  signup(data) {
    return this.$http('POST', `account/signup`, data)
  }

  checkEmail(email) {
    return this.$http('POST', `account/check-email`, { email: email })
  }

  resetPassword(email) {
    return this.$http('POST', `account/reset-password`, { email })
  }

  registerFromCart(cart_id, password) {
    return this.$http('POST', `carts/${cart_id}/register`, { password }).then((data) => {
      this.$account = data.account
      this.$app.config.account_id = this.$account.id
      this.$events.emitEvent('change', [this.$account])
      return Promise.resolve(data)
    })
  }
}

export default AccountEndpoint
