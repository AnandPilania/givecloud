import { rest } from 'msw'
import { mockOrgSettings } from '@/mocks/data'
import { ROUTES } from './routes'

const patchOrgSettings = (mockedResponse = {}) =>
  rest.patch(ROUTES.orgSettings, (_, response, { json }) =>
    response(
      json({
        data: mockOrgSettings({
          ...mockedResponse,
        }),
      })
    )
  )

export { patchOrgSettings }
