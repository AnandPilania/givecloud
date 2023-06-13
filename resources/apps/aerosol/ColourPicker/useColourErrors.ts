import { WHITE } from '@/shared/constants/theme'
import { isValidHexColor } from '@/shared/utilities'
import tinycolor from 'tinycolor2'

const useColourErrors = () => {
  const startsWithHash = (code: string) => code.charAt(0) === '#'

  const isCustomColourReadable = (code: string) =>
    tinycolor.isReadable(WHITE.code, code, {
      level: 'AA',
      size: 'large',
    })

  const getColourErrors = (code: string) => {
    if (!startsWithHash(code)) return ['Code needs to start with #']
    if (!code.length) return ['Field is required']
    if (!isValidHexColor(code)) return [`${code} is not valid`]
    if (!isCustomColourReadable(code)) return [`${code} fails contrast requirements`]
    return []
  }

  return {
    isCustomColourReadable,
    getColourErrors,
  }
}

export { useColourErrors }
