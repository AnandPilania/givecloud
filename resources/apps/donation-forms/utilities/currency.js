// polyfills are required for narrowSymbol support for versions of
// Safari prior to 14.1 and provide consistent formatting

import '@formatjs/intl-getcanonicallocales/polyfill'
import '@formatjs/intl-locale/polyfill'

import '@formatjs/intl-pluralrules/polyfill'
import '@formatjs/intl-pluralrules/locale-data/en'
import '@formatjs/intl-pluralrules/locale-data/es'
import '@formatjs/intl-pluralrules/locale-data/fr'

import '@formatjs/intl-numberformat/polyfill'
import '@formatjs/intl-numberformat/locale-data/en'
import '@formatjs/intl-numberformat/locale-data/es'
import '@formatjs/intl-numberformat/locale-data/fr'

import { escapeRegExp } from 'lodash'
import Givecloud from 'givecloud'
import getConfig from '@/utilities/config'
import { toNumber } from '@/utilities/number'

const config = getConfig()

export const LEFT_HAND_SIDE_PLACEMENT = 'left'
export const RIGHT_HAND_SIDE_PLACEMENT = 'right'

export const getCurrency = (currencyCode) => {
  return Givecloud.config.currencies.find((currency) => currencyCode === currency.code)
}

export const getCurrencySymbolPlacement = (locale, currencyCode) => {
  const currency = getCurrency(currencyCode)
  const formattedAmount = formatCurrency(10, currencyCode, {
    locale,
    showCurrencyCode: false,
    showCurrencySymbol: true,
  })

  return new RegExp(`^${escapeRegExp(currency.symbol)}`).test(formattedAmount)
    ? LEFT_HAND_SIDE_PLACEMENT
    : RIGHT_HAND_SIDE_PLACEMENT
}

export const formatCurrency = (amount, currencyCode = config.local_currency.code, options = {}) => {
  amount = toNumber(amount)

  const currencyOptions = {
    locale: Givecloud.config.locale.iso,
    abbreviate: false,
    autoFractionDigits: false,
    showCurrencyCode: false,
    showCurrencySymbol: true,
    useGrouping: true,
    ...options,
  }

  const numberFormatOptions = {
    style: 'currency',
    currency: currencyCode,
    currencyDisplay: 'narrowSymbol',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
    notation: currencyOptions.abbreviate ? 'compact' : 'standard',
    useGrouping: currencyOptions.useGrouping ? 'auto' : false,
  }

  if (currencyOptions.autoFractionDigits && (amount % 1 === 0 || amount == 0)) {
    numberFormatOptions.minimumFractionDigits = 0
    delete numberFormatOptions.maximumFractionDigits
  }

  if (currencyOptions.showCurrencySymbol === false) {
    delete numberFormatOptions.style
    delete numberFormatOptions.currency
    delete numberFormatOptions.currencyDisplay
  }

  const currencyFormatted = new Intl.NumberFormat(currencyOptions.locale, numberFormatOptions).format(amount)

  return currencyOptions.showCurrencyCode ? `${currencyFormatted} ${currencyCode}` : currencyFormatted
}

export default formatCurrency
