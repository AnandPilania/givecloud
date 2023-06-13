const formatMoney = (amount, currency, digits = 2, abbreviate = false) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency,
    currencyDisplay: 'symbol',
    ...(digits === null && { minimumFractionDigits: 0 }),
    ...(digits !== null && { minimumFractionDigits: digits, maximumFractionDigits: digits }),
    ...(abbreviate && { notation: 'compact' }),
  }).format(amount)
}

export const moneyFormatter = (amount, currency, format = 'default') => {
  const digits = format === 'compact' ? null : format === 'basic' ? 0 : 2
  const abbreviate = format === 'compact'

  return formatMoney(amount, currency, digits, abbreviate)
}

export default formatMoney
