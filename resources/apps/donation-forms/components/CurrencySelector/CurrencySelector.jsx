import PropTypes from 'prop-types'
import { useRecoilState, useRecoilValue } from 'recoil'
import SelectDropdown from '@/components/SelectDropdown/SelectDropdown'
import Givecloud from 'givecloud'
import configState from '@/atoms/config'
import currencyState from '@/atoms/currency'
import { TOP_CURRENCIES } from '@/shared/constants/topCurrencies'

const CurrencySelector = ({ className = '', clean = false }) => {
  const currencies = Givecloud.config.currencies
  const [currency, setCurrency] = useRecoilState(currencyState)
  const config = useRecoilValue(configState)

  const handleOnChange = (e) => {
    setCurrency(e.target.value)
  }

  const topCurrencies = currencies.filter((currency) => TOP_CURRENCIES.includes(currency.code))

  const isLocalCurrencyIncluded =
    topCurrencies.findIndex((currency) => currency.code === config.local_currency.code) !== -1

  const topCurrenciesAndLocalCurrency = [
    ...topCurrencies,
    ...(isLocalCurrencyIncluded ? [] : [{ ...config.local_currency }]),
  ]

  return (
    <div className={className}>
      {currencies.length < 2 && <input type='hidden' name='currency' value={currency.code} />}
      {currencies.length > 1 && (
        <SelectDropdown
          clean={clean}
          name='currency_code'
          defaultValue={currency.code}
          onChange={handleOnChange}
          options={topCurrenciesAndLocalCurrency.map(({ code, name }) => ({
            label: `${code} - ${name}`,
            value: code,
            selected: code,
          }))}
        />
      )}
    </div>
  )
}

CurrencySelector.propTypes = {
  className: PropTypes.string,
  clean: PropTypes.bool,
}

export default CurrencySelector
