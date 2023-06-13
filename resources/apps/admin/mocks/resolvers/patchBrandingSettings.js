import { rest } from 'msw'
import { mockBrandingSettings } from '@/mocks/data'
import { ROUTES } from './routes'

const patchBrandingSettings = (mockedResponse = {}) =>
  rest.patch(ROUTES.brandingSettings, (_, response, { json }) =>
    response(
      json({
        data: mockBrandingSettings({
          ...mockedResponse,
        }),
      })
    )
  )

export { patchBrandingSettings }
