export const templates = [
  {
    title: 'Standard',
    type: 'standard',
    subtitle: 'Our patented, standard fundraising experience optimized for trust,  engagement, and conversion.',
    isAvailable: true,
  },
  {
    title: 'Tiles',
    type: 'amount_tiles',
    subtitle:
      'An alternative to our standard experience displaying default amounts in tiles, and optimized for engagement and conversion.',
    isAvailable: true,
  },
  {
    title: 'Impact Subscriptions',
    type: 'impact_subscriptions',
    subtitle: 'This experience encourages loyal supporters to commit to fixed levels of impact.',
    isAvailable: false,
  },
  {
    title: 'Multi-Fund',
    type: 'multi_fund',
    subtitle: 'Allow supporters to swipe between fund designations.',
    isAvailable: false,
  },
  {
    title: 'Impact-First',
    type: 'impact_first',
    subtitle: 'Supporters choose how much impact they want to have, then discover how to finance that impact.',
    isAvailable: false,
  },
  {
    title: 'Split-Fund',
    type: 'split_fund',
    subtitle: 'Allow supporters to split their donation between multiple fund designations.',
    isAvailable: false,
  },
  {
    title: 'Impact-Tiles',
    type: 'impact_tiles',
    subtitle: 'Allow supporters to choose between tiles that represent impact.',
    isAvailable: false,
  },
] as const

export type Template = typeof templates[number]
