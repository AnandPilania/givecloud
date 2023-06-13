export default {
  config: {
    currencies: [
      {
        active: true,
        code: 'CAD',
        countries: ['CA'],
        default: true,
        default_country: 'CA',
        has_unique_symbol: false,
        iso_code: 'CAD',
        local: true,
        locale: 'en-CA',
        name: 'Canadian Dollar',
        rate: 1,
        symbol: '$',
        unique_symbol: 'CAD',
      },
      {
        active: true,
        code: 'GBP',
        countries: ['IM', 'JE', 'GS', 'GB'],
        default: false,
        default_country: 'GB',
        has_unique_symbol: false,
        iso_code: 'GBP',
        local: true,
        locale: 'en-IM',
        name: 'British Pound Sterling',
        rate: 0.592022,
        symbol: '£',
        unique_symbol: 'GBP',
      },
      {
        active: true,
        code: 'HKD',
        countries: ['HK'],
        default: false,
        default_country: 'HK',
        has_unique_symbol: false,
        iso_code: 'HKD',
        local: true,
        locale: 'en-HK',
        name: 'Hong Kong Dollar',
        rate: 5.764647,
        symbol: '$',
        unique_symbol: 'HKD',
      },
    ],
  },
}
