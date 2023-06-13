import { toNumber } from 'lodash'
import Givecloud from 'givecloud'

export { toNumber }

export const formatNumber = (value, options = {}) => {
  value = toNumber(value)

  const formatOptions = {
    abbreviate: false,
    useGrouping: true,
    ...options,
  }

  const numberFormatOptions = {
    notation: formatOptions.abbreviate ? 'compact' : 'standard',
    useGrouping: formatOptions.useGrouping ? 'auto' : false,
  }

  return new Intl.NumberFormat(Givecloud.config.locale.iso, numberFormatOptions).format(value)
}

export default formatNumber
