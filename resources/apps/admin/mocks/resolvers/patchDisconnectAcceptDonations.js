import { rest } from 'msw'
import { mockAcceptDonations } from '@/mocks/data'
import { ROUTES } from './routes'

export const patchDisconnectAcceptDonations = (mockedResponse = {}) =>
  rest.patch(ROUTES.disconnectAcceptDonations, (_, response, { json }) =>
    response(
      json({
        data: mockAcceptDonations({
          ...mockedResponse,
        }),
      })
    )
  )
