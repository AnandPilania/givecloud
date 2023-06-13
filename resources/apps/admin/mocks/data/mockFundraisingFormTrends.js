export const mockFundraisingFormTrends = (options = {}) => ({
  trends: {
    revenues: {
      data: {
        '2022-09-05': 0,
        '2022-09-06': 0,
      },
      trend: -1,
      previousPeriod: 0,
      lastPeriod: 0,
    },
    views: {
      data: {
        '2022-09-05': 0,
        '2022-09-06': 0,
      },
      trend: -1,
      previousPeriod: 0,
      lastPeriod: 0,
    },
    donors: {
      trend: -1,
      previousPeriod: 0,
      lastPeriod: 0,
    },
    conversions: {
      trend: -1,
      previousPeriod: 0,
      lastPeriod: 0,
    },
  },
  ...options,
})
