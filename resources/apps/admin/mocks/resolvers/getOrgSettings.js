import { rest } from 'msw'
import { mockOrgSettings } from '@/mocks/data'
import { ROUTES } from './routes'

const getOrgSettings = (mockedResponse = {}) =>
  rest.get(ROUTES.orgSettings, (_, response, { json }) =>
    response(
      json({
        data: mockOrgSettings({
          ...mockedResponse,
        }),
      })
    )
  )

export { getOrgSettings }
