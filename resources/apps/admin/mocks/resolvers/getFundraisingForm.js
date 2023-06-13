import { rest } from 'msw'
import { mockFundraisingForm, mockFundraisingFormStats } from '@/mocks/data'
import { ROUTES } from './routes'
import { mockFundraisingFormTrends } from '../data/mockFundraisingFormTrends'

const getFundraisingForm = (mockedResponse = {}) =>
  rest.get(ROUTES.fundraisingForm, (request, response, { json, status }) => {
    const isIncludingStats = request.url.searchParams.get('include_stats')
    const { id } = request.params

    if (!id) return response(status(404))

    if (isIncludingStats) {
      return response(
        json({
          data: mockFundraisingForm({
            ...mockFundraisingFormStats({
              ...mockFundraisingFormTrends(),
            }),
            ...mockedResponse,
          }),
        })
      )
    }
    return response(
      json({
        data: mockFundraisingForm({ ...mockedResponse }),
      })
    )
  })

export { getFundraisingForm }
