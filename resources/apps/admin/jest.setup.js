const { selector, snapshot_UNSTABLE } = require('recoil')
import crypto from 'crypto'
import 'whatwg-fetch'
import { queryClient, server } from '@/mocks/setup'

Object.defineProperty(window, 'crypto', {
  get() {
    return crypto.webcrypto
  },
})

const clearSelectorCachesState = selector({
  key: 'ClearSelectorCaches',
  get: ({ getCallback }) =>
    getCallback(({ snapshot, refresh }) => () => {
      for (const node of snapshot.getNodes_UNSTABLE()) {
        refresh(node)
      }
    }),
})

const initialSnapshot = snapshot_UNSTABLE()
const clearSelectorCaches = initialSnapshot.getLoadable(clearSelectorCachesState).getValue()

const defaultConfig = { currency: { code: 'USD' } }
window.adminSpaData = { ...defaultConfig }

window.IntersectionObserver = jest.fn().mockImplementation(() => ({
  observe: () => null,
  disconnect: () => null,
}))

window.ResizeObserver = jest.fn().mockImplementation(() => ({
  observe: () => null,
  disconnect: () => null,
  unobserve: () => null,
}))

// Selector caches are shared between <RecoilRoot>'s and tests, so we need to
// make sure the cache is clearred before each test
global.beforeEach(() => {
  window.adminSpaData = { ...defaultConfig }
  clearSelectorCaches()
})

beforeAll(() => server.listen())
afterEach(() => {
  server.resetHandlers()
  queryClient.clear()
})
afterAll(() => server.close())
