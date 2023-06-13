import Endpoint from '@core/endpoint'

class FundraisingPagesEndpoint extends Endpoint {
  get() {
    return this.$http('GET', `fundraising-pages`)
  }

  find(id) {
    return this.$http('GET', `fundraising-pages/${id}`).then((data) => {
      return Promise.resolve(data.fundraising_page)
    })
  }

  create(data) {
    return this.$http('POST', `fundraising-pages`, data).then((data) => {
      return Promise.resolve(data.fundraising_page)
    })
  }

  update(id, data) {
    return this.$http('PATCH', `fundraising-pages/${id}`, data).then((data) => {
      return Promise.resolve(data.fundraising_page)
    })
  }

  delete(id) {
    return this.$http('DELETE', `fundraising-pages/${id}`)
  }
}

export default FundraisingPagesEndpoint
