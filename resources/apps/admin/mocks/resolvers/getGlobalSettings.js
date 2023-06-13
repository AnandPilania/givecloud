import { rest } from 'msw'
import { mockGlobalSettings } from '@/mocks/data'
import { ROUTES } from './routes'

const getGlobalSettings = (mockedResponse = {}) =>
  rest.get(ROUTES.globalSettings, (_, response, { json }) =>
    response(
      json({
        data: mockGlobalSettings({
          ...mockedResponse,
        }),
      })
    )
  )
export { getGlobalSettings }
