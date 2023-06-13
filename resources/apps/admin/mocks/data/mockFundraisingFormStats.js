export const mockFundraisingFormStats = (options = {}) => ({
  stats: {
    donorCount: 4,
    revenueAmount: 500,
    currency: 'USD',
    ...options,
  },
})
