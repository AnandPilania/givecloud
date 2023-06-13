import { render } from '@testing-library/react'
import { BrowserRouter } from 'react-router-dom'
import { RecoilRoot } from 'recoil'
import { Router } from './Router'
import { PageNotFound } from '@/screens/PageNotFound'
import { QueryClient, QueryClientProvider } from 'react-query'
import { Layout } from '@/screens/Layout'
import { BASE_ADMIN_PATH } from '@/constants/pathConstants'
import { setConfig } from '@/utilities/config'
import { MockComponent } from '@/utilities/MockComponent'

const queryClient = new QueryClient()
jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  BrowserRouter: jest.fn((props) => MockComponent(props)),
  Switch: jest.fn((props) => MockComponent(props)),
  Route: jest.fn((props) => MockComponent(props)),
}))
jest.mock('axios')
jest.mock('@/screens/Layout', () => ({
  Layout: jest.fn((props) => MockComponent(props)),
}))
jest.mock('@/screens/PageNotFound', () => ({
  PageNotFound: jest.fn((props) => MockComponent(props)),
}))

afterEach(() => {
  jest.clearAllMocks()
})

test('renders expected BrowserRouter', () => {
  setConfig({ initialAppSource: 'SPA' })

  render(
    <RecoilRoot>
      <QueryClientProvider client={queryClient}>
        <Router />
      </QueryClientProvider>
    </RecoilRoot>
  )

  const browserRouterProps = BrowserRouter.mock.calls[0][0]

  expect(browserRouterProps.basename).toEqual(BASE_ADMIN_PATH)
})

test('renders a Layout', () => {
  setConfig({ initialAppSource: 'SPA' })

  render(
    <RecoilRoot>
      <QueryClientProvider client={queryClient}>
        <Router />
      </QueryClientProvider>
    </RecoilRoot>
  )

  expect(Layout).toHaveBeenCalled()
})

test('renders a route for PageNotFound if appSource is not "laravel"', () => {
  setConfig({ initialAppSource: 'SPA' })

  render(
    <RecoilRoot>
      <QueryClientProvider client={queryClient}>
        <Router />
      </QueryClientProvider>
    </RecoilRoot>
  )

  const pageNotFoundInstances = PageNotFound.mock.calls

  expect(pageNotFoundInstances).toHaveLength(1)
})

test('does not render a route for PageNotFound if appSource is laravel', () => {
  setConfig({ initialAppSource: 'laravel' })

  render(
    <RecoilRoot>
      <Router />
    </RecoilRoot>
  )

  const pageNotFoundInstances = PageNotFound.mock.calls

  expect(pageNotFoundInstances).toHaveLength(0)
})
