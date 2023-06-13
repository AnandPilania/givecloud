export const mockAcceptDonations = (options = {}) => ({
  stripe: {
    isEnabled: false,
    isAchAllowed: false,
    isWalletPayAllowed: false,
    isMulticurrencySupported: false,
  },
  paypal: {
    isEnabled: false,
  },
  venmo: {
    isEnabled: false,
  },
  ...options,
})
