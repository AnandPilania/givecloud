import { rest } from 'msw'
import { mockFundraisingForm } from '@/mocks/data'
import { ROUTES } from './routes'

const postFundraisingForm = (mockedResponse = {}) =>
  rest.post(ROUTES.fundraisingForms, (_, response, { json }) => {
    return response(
      json({
        data: mockFundraisingForm({
          ...mockedResponse,
        }),
      })
    )
  })

export { postFundraisingForm }
