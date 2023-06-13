import { rest } from 'msw'
import { mockFundraisingForm, mockFundraisingFormStats } from '@/mocks/data'
import { ROUTES } from './routes'

const getFundraisingForms = (mockedResponse = {}) =>
  rest.get(ROUTES.fundraisingForms, (request, response, { json }) => {
    const isIncludingStats = request.url.searchParams.get('include_stats')
    const isArchivedForms = request.url.searchParams.get('archived')

    if (isArchivedForms && isIncludingStats) {
      const data = mockedResponse ?? []

      return response(
        json({
          data,
        })
      )
    }

    if (isIncludingStats) {
      return response(
        json({
          data: [
            mockFundraisingForm({
              id: 'one',
              ...mockFundraisingFormStats(),
            }),
            mockFundraisingForm({
              id: 'two',
              ...mockFundraisingFormStats(),
            }),
            mockFundraisingForm({
              id: 'three',
              ...mockFundraisingFormStats(),
              ...mockedResponse,
            }),
          ],
        })
      )
    }

    return response(
      json({
        data: [mockFundraisingForm({ ...mockedResponse })],
      })
    )
  })

export { getFundraisingForms }
