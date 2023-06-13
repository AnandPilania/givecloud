import tinycolor from 'tinycolor2'
import getConfig from './config'

const config = getConfig()
const primaryColour = tinycolor(config.primary_colour || '#3b82f6')

export const isPrimaryColourDark = primaryColour.getBrightness() < 150

export const primaryColour50 = primaryColour.clone().lighten(35).toHexString() // prettier-ignore
export const primaryColour100 = primaryColour.clone().lighten(30).toHexString() // prettier-ignore
export const primaryColour200 = primaryColour.clone().lighten(25).toHexString() // prettier-ignore
export const primaryColour300 = primaryColour.clone().lighten(20).toHexString() // prettier-ignore
export const primaryColour400 = primaryColour.clone().lighten(10).toHexString() // prettier-ignore
export const primaryColour500 = primaryColour.clone().toHexString() // prettier-ignore
export const primaryColour600 = primaryColour.clone().darken(10).toHexString() // prettier-ignore
export const primaryColour700 = primaryColour.clone().darken(20).toHexString() // prettier-ignore
export const primaryColour800 = primaryColour.clone().darken(25).toHexString() // prettier-ignore
export const primaryColour900 = primaryColour.clone().darken(30).toHexString() // prettier-ignore

export const primaryColourWhiteOrBlack = isPrimaryColourDark ? '#fff' : '#000'

export const primaryColourOrBlack = isPrimaryColourDark ? primaryColour500 : '#000'

export const primaryColours = [
  primaryColour50,
  primaryColour100,
  primaryColour200,
  primaryColour300,
  primaryColour400,
  primaryColour500,
  primaryColour600,
  primaryColour700,
  primaryColour800,
  primaryColour900,
]

const colourToRgbValues = (colour) => {
  const rgbColour = colour.toRgb()
  return `${rgbColour.r},${rgbColour.g},${rgbColour.b}`
}

const getAppBackground = () => {
  if (config.widget_type === 'modal_embed') {
    return 'none'
  }

  if (config.background_url) {
    return `url(${config.background_url})`
  }

  if (config.widget_type === 'hosted_page' && config.layout === 'standard') {
    return `linear-gradient(${primaryColour500}, ${primaryColour700})`
  }

  return 'none'
}

const getAppBackgroundColour = () => {
  if (config.widget_type !== 'hosted_page') {
    return 'transparent'
  }

  return config.layout === 'standard' ? primaryColour : '#e2e8f0'
}

// prettier-ignore
export const cssVariables = {
  '--app-bg-colour': getAppBackgroundColour(),
  '--app-bg-image': getAppBackground(),
  '--primary-colour-50': primaryColour50,
  '--primary-colour-100': primaryColour100,
  '--primary-colour-200': primaryColour200,
  '--primary-colour-300': primaryColour300,
  '--primary-colour-400': primaryColour400,
  '--primary-colour-500': primaryColour500,
  '--primary-colour-600': primaryColour600,
  '--primary-colour-700': primaryColour700,
  '--primary-colour-800': primaryColour800,
  '--primary-colour-900': primaryColour900,
  '--primary-colour-500-rgb': colourToRgbValues(primaryColour.clone()),
  '--primary-colour-white-or-black': primaryColourWhiteOrBlack,
  '--primary-text-colour': primaryColourOrBlack,
}
