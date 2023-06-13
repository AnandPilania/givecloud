import { rest } from 'msw'
import { mockFundraisingForm } from '@/mocks/data'
import { ROUTES } from './routes'

const patchFundraisingForm = (mockedResponse = {}) =>
  rest.patch(ROUTES.fundraisingForm, (_, response, { json }) => {
    return response(
      json({
        data: mockFundraisingForm({ ...mockedResponse }),
      })
    )
  })

export { patchFundraisingForm }
