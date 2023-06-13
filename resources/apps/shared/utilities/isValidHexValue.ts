const hexColorRegex = /^#?([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/

export const isValidHexColor = (colour: string) => hexColorRegex.test(colour)
