import LogRocket from 'logrocket'
import Givecloud from 'givecloud'
import getConfig from '@/utilities/config'

const isProduction = process.env.NODE_ENV === 'production'

export const initLogRocket = () => {
  const config = getConfig()

  if (isProduction) {
    LogRocket.init('rouoyn/fundraising-forms', {
      console: {
        isEnabled: {
          log: false,
          debug: false,
        },
      },
      network: {
        isEnabled: false,
      },
    })

    LogRocket.identify(null, {
      site: Givecloud.config.site,
      widget_type: config.widget_type,
      template: config.template,
    })
  }
}
