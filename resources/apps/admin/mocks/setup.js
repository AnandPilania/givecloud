import { Suspense } from 'react'
import { RecoilRoot } from 'recoil'
import { render as renderRTL, screen as screenRTL, waitForElementToBeRemoved } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from 'react-query'
import { server } from './server'
import { renderHook } from '@testing-library/react-hooks'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { createSetters } from '@/mocks/handlers'
import { ToastContainer } from '@/aerosol'
import { setConfig } from '@/utilities/config'
import { Loading } from '@/screens/Loading'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: false,
    },
  },
  logger: {
    log: console.log,
    warn: console.warn,
    error: () => {},
  },
})

const mockTailwindHook = (viewportWidth = 1400) => {
  window.innerWidth = viewportWidth
  let tailwindBreakPoints
  renderHook(() => (tailwindBreakPoints = useTailwindBreakpoints()))
  return { tailwindBreakPoints }
}

const render = (ui, options = {}) => {
  const { config, initialEntries, viewportWidth } = options
  const { use } = server

  setConfig({ currency: { code: 'USD' }, ...config })
  const { tailwindBreakPoints } = mockTailwindHook(viewportWidth)

  const wrapper = ({ children }) => (
    <QueryClientProvider client={queryClient}>
      <RecoilRoot>
        <MemoryRouter initialEntries={initialEntries}>
          <Suspense fallback={<Loading />}>{children}</Suspense>
          <ToastContainer />
        </MemoryRouter>
      </RecoilRoot>
    </QueryClientProvider>
  )
  const mockQueryResult = (query) => renderHook(() => query(), { wrapper })
  const renderScreen = () => renderRTL(ui, { wrapper, ...options })

  const waitForLoadingToBeFinished = async () => {
    expect(screenRTL.getByTestId('loading')).toBeInTheDocument()
    await waitForElementToBeRemoved(() => screenRTL.getByTestId('loading'))
  }

  return {
    tailwindBreakPoints,
    renderScreen,
    mockQueryResult,
    waitForLoadingToBeFinished,
    ...createSetters(use),
  }
}
export { server }
export { queryClient }
export { render }
export * from '@testing-library/react'
