export enum SCREEN {
  DONATION = '0',
  REMINDER = '1',
  UPSELL = '2',
  EMPLOYER = '3',
  EMAIL_OPT_IN = '4',
  THANK_YOU = '5',
}

export enum TAB {
  TEMPLATE = '0',
  LAYOUT = '1',
  EXPERIENCE = '2',
  SHARING = '3',
  EMAIL = '4',
}

export enum NAVIGATION {
  'screen',
  'tab',
}

export type NavigationType = keyof typeof NAVIGATION
export type Screens = keyof typeof SCREEN
export type Tabs = keyof typeof SCREEN
