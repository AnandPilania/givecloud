const getDefaultCurrency = (code, currencies) => {
  return currencies.find((currency) => currency.code === code)
}

export default getDefaultCurrency
