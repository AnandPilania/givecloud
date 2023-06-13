import tinycolor from 'tinycolor2'
import { isValidHexColor } from './isValidHexValue'
import { BLUE, CUSTOM as CUSTOM_FALLBACK_COLOUR } from '../constants/theme'

interface Theme {
  alpha?: number
  darken?: number
  colour?: string
}

export const setRootThemeColour = ({ colour = BLUE.code, alpha = 0.15, darken = 10 }: Theme) => {
  const isValidColour = isValidHexColor(colour)

  const lightThemeColour = tinycolor(colour).setAlpha(alpha).toRgbString()
  const lightFallBackColour = tinycolor(CUSTOM_FALLBACK_COLOUR.code).setAlpha(alpha).toRgbString()

  const darkThemeColour = tinycolor(colour).darken(darken).toString()
  const darkFallBackColour = tinycolor(CUSTOM_FALLBACK_COLOUR.code).darken(darken).toString()

  document.documentElement.style.setProperty('--theme--colour', isValidColour ? colour : CUSTOM_FALLBACK_COLOUR.code)

  document.documentElement.style.setProperty(
    '--theme--colour-light',
    isValidColour ? lightThemeColour : lightFallBackColour
  )

  document.documentElement.style.setProperty(
    '--theme--colour-dark',
    isValidColour ? darkThemeColour : darkFallBackColour
  )
}
