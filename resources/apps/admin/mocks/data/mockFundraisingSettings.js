export const mockFundraisingSettings = (options = {}) => ({
  orgSupportNumber: '4164536278',
  orgSupportNumberCountryCode: 'Canada',
  orgSupportEmail: 'pizza@pizza.com',
  orgOtherWaysToDonate: [{ id: 1, label: 'spaghetti', href: 'meatballs.org' }],
  orgFaqAlternativeQuestion: 'Where should I donate?',
  orgFaqAlternativeAnswer: 'Online.',
  orgCheckMailingAddress: '101 Pizza Lane',
  orgPrivacyOfficerEmail: 'officer@privacy.ca',
  orgPrivacyPolicyUrl: 'www.privvy.org',
  ...options,
})
