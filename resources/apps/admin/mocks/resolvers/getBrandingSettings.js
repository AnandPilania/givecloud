import { rest } from 'msw'
import { mockBrandingSettings } from '@/mocks/data'
import { ROUTES } from './routes'

const getBrandingSettings = (mockedResponse = {}) =>
  rest.get(ROUTES.brandingSettings, (_, response, { json }) =>
    response(
      json({
        data: mockBrandingSettings({
          ...mockedResponse,
        }),
      })
    )
  )

export { getBrandingSettings }
