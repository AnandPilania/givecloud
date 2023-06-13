import { rest } from 'msw'
import { ROUTES } from './routes'

export const postRestoreFundraisingForm = (mockedResponse = {}) =>
  rest.post(ROUTES.restoreFundraisingForm, (_, response, { json }) =>
    response(
      json({
        data: { ...mockedResponse },
      })
    )
  )
