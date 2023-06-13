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

const trimZero = (formattedAmount: string) => formattedAmount.replace('0', '')

interface Options extends Intl.NumberFormatOptions {
  amount: number
  digits?: number
  showZero?: boolean
}

const formatMoney = ({
  amount,
  currency = 'USD',
  digits = 2,
  showZero = false,
  notation = 'standard',
  ...options
}: Options) => {
  const fractionDigits =
    notation !== 'compact' ? { maximumFractionDigits: digits, minimumFractionDigits: digits } : null

  const formattedAmount = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency,
    currencyDisplay: 'narrowSymbol',
    notation,
    ...fractionDigits,
    ...options,
  }).format(amount)

  if (amount) {
    return formattedAmount
  }

  return showZero ? formattedAmount : trimZero(formattedAmount)
}

export { formatMoney }
