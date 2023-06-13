import { rest } from 'msw'
import { mockFundraisingSettings } from '@/mocks/data'
import { ROUTES } from './routes'

const getFundraisingSettings = (mockedResponse = {}) =>
  rest.get(ROUTES.fundraisingSettings, (_, response, { json }) =>
    response(
      json({
        data: mockFundraisingSettings({
          ...mockedResponse,
        }),
      })
    )
  )
export { getFundraisingSettings }
