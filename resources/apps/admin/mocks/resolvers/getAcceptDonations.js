import { rest } from 'msw'
import { mockAcceptDonations } from '@/mocks/data'
import { ROUTES } from './routes'

const getAcceptDonations = (mockedResponse = {}) =>
  rest.get(ROUTES.acceptDonations, (_, response, { json }) =>
    response(
      json({
        data: mockAcceptDonations({
          ...mockedResponse,
        }),
      })
    )
  )

export { getAcceptDonations }
