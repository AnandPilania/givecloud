import { rest } from 'msw'
import { mockFundraisingSettings } from '@/mocks/data'
import { ROUTES } from './routes'

const patchFundraisingSettings = (mockedResponse = {}) =>
  rest.patch(ROUTES.fundraisingSettings, (_, response, { json }) =>
    response(
      json({
        data: mockFundraisingSettings({
          ...mockedResponse,
        }),
      })
    )
  )

export { patchFundraisingSettings }
