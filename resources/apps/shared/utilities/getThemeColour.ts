import { BLUE, COLOURS } from '../constants/theme'

const getThemeColour = (hexCode = BLUE.code) => {
  const recommendedColour = COLOURS.find(({ code }) => code.toLowerCase() === hexCode?.toLowerCase())

  return !!recommendedColour
    ? recommendedColour
    : {
        value: 'custom',
        code: hexCode,
      }
}

export { getThemeColour }
