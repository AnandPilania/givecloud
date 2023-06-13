import Endpoint from '@core/endpoint'

class CsrfTokenEndpoint extends Endpoint {
  check() {
    return this.$http('GET', 'csrf-token').then((data) => {
      if (data && data.token) {
        this.$app.setCsrfToken(data.token)
      }
    })
  }
}

export default CsrfTokenEndpoint
