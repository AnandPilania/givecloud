export const PRIMARY = 'primary'
export const SECONDARY = 'secondary'
export const INFO = 'info'
export const WARNING = 'warning'
export const ERROR = 'error'
export const LIGHT = 'light'
export const SUCCESS = 'success'
export const CUSTOM_THEME = 'custom'

export const THEMES = [PRIMARY, SECONDARY, INFO, WARNING, ERROR, LIGHT, SUCCESS, CUSTOM_THEME] as const

export const PINK = {
  value: 'pink',
  code: '#DC529B',
}
export const PURPLE = {
  value: 'purple',
  code: '#695DB1',
}
export const BLUE = {
  value: 'blue',
  code: '#2467CC',
}
export const ORANGE = {
  value: 'orange',
  code: '#CF823A',
}
export const RED = {
  value: 'red',
  code: '#CB4F4F',
}
export const GREEN = {
  value: 'green',
  code: '#2B957E',
}

export const BLACK = {
  value: 'black',
  code: '#333333',
}

export const CUSTOM = {
  value: 'custom',
  code: '#4B5563',
}

export const WHITE = {
  value: 'white',
  code: '#FFF',
}

export const COLOURS = [BLUE, GREEN, ORANGE, RED, PINK, PURPLE, BLACK, CUSTOM] as const
export const COLUMN_SIZES = ['one', 'two', 'three', 'four', 'five', 'six', 'small'] as const
export const TEXT_TYPES = ['h1', 'h2', 'h3', 'h4', 'h5', 'p', 'footnote'] as const

export type ThemeType = typeof THEMES[number]
export type ColumnSize = typeof COLUMN_SIZES[number]
export type TextType = typeof TEXT_TYPES[number]
export type ColoursType = typeof COLOURS[number]
