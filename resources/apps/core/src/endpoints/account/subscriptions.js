import Endpoint from '@core/endpoint'

class SubscriptionsEndpoint extends Endpoint {
  get() {
    return this.$http('GET', `account/subscriptions`)
  }

  find(id) {
    return this.$http('GET', `account/subscriptions/${id}`)
  }

  update(id, data) {
    return this.$http('PATCH', `account/subscriptions/${id}`, data)
  }

  cancel(id, data) {
    return this.$http('DELETE', `account/subscriptions/${id}`, data)
  }
}

export default SubscriptionsEndpoint
