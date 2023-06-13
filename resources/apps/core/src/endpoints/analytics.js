import Endpoint from '@core/endpoint'
import { v4 as uuidv4 } from 'uuid'
import { getUrlParameter } from '@core/utils'
import { debounce } from 'lodash'

class AnalyticsEndpoint extends Endpoint {
  constructor(app) {
    super(app)

    this.$queuedEvents = []
    this.$debounceSubmitEvents = debounce(this.$submitEvents.bind(this), 1500)
  }

  event(data) {
    this.$queuedEvents.push({
      visitor_id: this.$getVisitorId(),
      utm_source: getUrlParameter('utm_source') || null,
      utm_medium: getUrlParameter('utm_medium') || null,
      utm_campaign: getUrlParameter('utm_campaign') || null,
      utm_term: getUrlParameter('utm_term') || null,
      utm_content: getUrlParameter('utm_content') || null,
      timestamp: new Date().toJSON(),
      ...data,
    })

    this.$debounceSubmitEvents()
  }

  $getVisitorId() {
    return localStorage.getItem('givecloud_visitor') || this.$resetVisitorId()
  }

  $resetVisitorId() {
    localStorage.setItem('givecloud_visitor', uuidv4())
    return localStorage.getItem('givecloud_visitor')
  }

  async $submitEvents() {
    const events = this.$queuedEvents

    this.$queuedEvents = []
    this.$http('POST', 'collect', { events })
  }
}

export default AnalyticsEndpoint
