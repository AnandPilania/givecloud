import Endpoint from '@core/endpoint'
import LocaleEndpoint from './services/locale'

class ServicesEndpoint extends Endpoint {
  constructor(app) {
    super(app)

    this.Locale = new LocaleEndpoint(app)
  }
}

export default ServicesEndpoint
