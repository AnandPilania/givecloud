export const widgets = [
  {
    type: 'popup',
    title: 'Instant Pop-Up',
    subtitle: 'Instantly load your fundraising experience on your website without redirecting your donors.',
    isAvailable: true,
  },
  {
    type: 'inline',
    title: 'Inline Experience',
    subtitle: 'Place your fundraising experience anywhere on your page.',
    isAvailable: true,
  },
  {
    type: 'thermometer',
    title: 'Thermometer',
    subtitle: 'Display your fundraising progress for your supporters to see.',
    isAvailable: false,
  },
  {
    type: 'honorRoll',
    title: 'Honor Roll',
    subtitle: 'Display a list of all recent donors to your fundraising experience. ',
    isAvailable: false,
  },
] as const
