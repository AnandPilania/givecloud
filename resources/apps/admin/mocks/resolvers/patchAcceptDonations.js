import { rest } from 'msw'
import { mockAcceptDonations } from '@/mocks/data'
import { ROUTES } from './routes'

const patchAcceptDonations = (mockedResponse = {}) =>
  rest.patch(ROUTES.acceptDonations, (_, response, { json }) =>
    response(
      json({
        data: mockAcceptDonations({
          ...mockedResponse,
        }),
      })
    )
  )

export { patchAcceptDonations }
