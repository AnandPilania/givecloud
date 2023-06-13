import Endpoint from '@core/endpoint'
import Lockr from 'lockr'

class LocaleEndpoint extends Endpoint {
  countries() {
    let countries = Lockr.get('gc:services/locale/countries(rev10)')
    if (countries) {
      return Promise.resolve(countries)
    }
    return this.$http('GET', 'services/locale/countries').then((data) => {
      Lockr.set('gc:services/locale/countries(rev10)', data)
      return Promise.resolve(data)
    })
  }

  subdivisions(country) {
    if (!country || country === 'IL') {
      return Promise.resolve({
        subdivisions: [],
        subdivision_type: 'Province',
        html: '',
      })
    }
    let subdivisions = Lockr.get(`gc:services/locale/${country}/subdivisions(rev4)`)
    if (subdivisions) {
      return Promise.resolve(subdivisions)
    }
    return this.$http('GET', `services/locale/${country}/subdivisions`).then((data) => {
      Lockr.set(`gc:services/locale/${country}/subdivisions(rev4)`, data)
      return Promise.resolve(data)
    })
  }
}

export default LocaleEndpoint
