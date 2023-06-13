import { RecoilRoot } from 'recoil'
import { QueryClient, QueryClientProvider, QueryCache } from 'react-query'
import { ReactQueryDevtools } from 'react-query/devtools'
import LogRocket from 'logrocket'
import { triggerToast } from '@/aerosol'
import getConfig from '@/utilities/config'

import { injectStyle } from 'react-toastify/dist/inject-style'
import { Router } from '../Router'

const { accountName, enableLogRocket, isGivecloudExpress, userEmail, userFullName } = getConfig()

if (enableLogRocket) {
  LogRocket.init('rouoyn/givecloud', {
    console: {
      isEnabled: {
        log: false,
        debug: false,
      },
    },
    dom: {
      inputSanitizer: true,
    },
    network: {
      isEnabled: false,
    },
  })

  LogRocket.identify(userEmail, {
    name: userFullName,
    email: userEmail,
    site: accountName,
    is_givecloud_express: isGivecloudExpress,
  })
}

const queryClient = new QueryClient({
  queryCache: new QueryCache({
    onError: () =>
      triggerToast({
        type: 'error',
        header: 'Sorry, there was a problem loading the page.',
        description: 'Please refresh the page and try again.',
        options: { autoClose: false },
      }),
  }),
})

const App = () => {
  // react-toastify styles
  injectStyle()

  return (
    <QueryClientProvider client={queryClient}>
      <RecoilRoot>
        <Router />
      </RecoilRoot>
      <ReactQueryDevtools initialIsOpen={false} />
    </QueryClientProvider>
  )
}

export { App }
