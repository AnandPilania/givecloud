const formatMoney = (amount) => {
  const formatConfig = {
    style: 'currency',
    currency: 'USD', // CNY for Chinese Yen, EUR for Euro
    minimumFractionDigits: 2,
    currencyDisplay: 'symbol',
  }

  // setup formatters
  const americanNumberFormatter = new Intl.NumberFormat('en-US', formatConfig)

  // use formatters
  return americanNumberFormatter.format(amount)
}

export default formatMoney
