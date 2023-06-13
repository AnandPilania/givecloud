import axios from 'axios'
import axiosJsonp from 'axios-jsonp'
import Validator from 'validatorjs'
import ValidatorMessages from 'validatorjs/src/messages'
import messages from 'validatorjs/src/lang/en'

function errorHandler(error) {
  return (err) => {
    try {
      if (err.response && err.response.data) {
        error = new Error(err.response.data.error || err.response.data.message || err.message || error)
        error.data = err.response.data
      } else {
        error = new Error(err.message || error)
      }
    } catch (e) {
      error = new Error(error)
    }
    return Promise.reject(error)
  }
}

class Endpoint {
  constructor(app) {
    this.$app = app
  }

  $http(method, uri, data = undefined) {
    let options = {
      baseURL: `https://${this.$app.config.host}/gc-json/${this.$app.config.version}/`,
      //timeout: 3000,
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-Locale': `${this.$app.config.locale.iso}`,
      },
      responseType: 'json',
    }
    if (this.$app.config.api_key) {
      options.headers['Authorization'] = `Bearer ${this.$app.config.api_key}`
    }
    let config = {
      method,
      url: uri,
      data,
    }
    return axios
      .create(options)
      .request(config)
      .then((res) => res.data, errorHandler('Unkown error (422).'))
  }

  $validator(data, rules, customMessages = undefined) {
    let validator = new Validator(data, rules, customMessages)
    validator.messages = new ValidatorMessages('en', messages) //Monkey patch to fix problem cause by dynamic requires
    return validator
  }

  /**
   * Submit a JSONP request.
   *
   * @param string url
   * @param object data
   */
  $jsonp(url, data) {
    return axios({
      url,
      params: data,
      adapter: axiosJsonp,
    }).then((res) => res.data, errorHandler('Unkown error (422).'))
  }
}

export default Endpoint
