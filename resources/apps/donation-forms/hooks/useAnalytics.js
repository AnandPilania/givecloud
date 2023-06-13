import { useRecoilValue } from 'recoil'
import LogRocket from 'logrocket'
import Givecloud from 'givecloud'
import configState from '@/atoms/config'

const events = []

const useAnalytics = (defaults = {}) => {
  const config = useRecoilValue(configState)

  return (data, options = {}) => {
    const { collectOnce = false, hostedPageOnly = false } = { ...defaults, ...options }

    const payload = {
      eventable: config.eventable,
      event_category: `fundraising_forms.${config.widget_type}`,
      ...data,
    }

    const notHostedPage = config.widget_type !== 'hosted_page'
    const alreadyCollected = events.find((event) => event.event_name === payload.event_name)

    if ((hostedPageOnly && notHostedPage) || (collectOnce && alreadyCollected)) {
      return
    }

    events.push(payload)
    Givecloud.Analytics.event(payload)

    LogRocket.track(payload.event_name, {
      ...(payload.event_value && { value: payload.event_value }),
    })

    if (window.gtag) {
      window.gtag('event', payload.event_name, {
        event_category: payload.event_category,
      })
    }
  }
}

export default useAnalytics
