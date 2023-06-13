import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Select from '@/fields/Select/Select'
import styles from '@/components/CurrencySelector/CurrencySelector.scss'

const CurrencySelector = () => {
  const { api, currency, payment } = useContext(StoreContext)
  const hasMultipleCurrencies = currency.all.length > 1

  if (!hasMultipleCurrencies) {
    return null
  }

  const handleChange = (e) => {
    const gateway = api.PaymentTypeGateway('bank_account')
    const currencyCode = currency.all.find((currency) => currency.code === e.target.value)

    currency.set(currencyCode)

    if (payment.method.chosen === 'bank_account' && !gateway.canMakeAchPayment(currencyCode)) {
      payment.method.set('credit_card')
    }
  }

  return (
    <div className={styles.root}>
      <Select onChange={handleChange} width='2/3' value={currency.chosen.code}>
        {currency.all.map((currency) => (
          <option key={currency.code} value={currency.code}>
            {currency.code} ({currency.symbol}) - {currency.name}
          </option>
        ))}
      </Select>
    </div>
  )
}

export default memo(CurrencySelector)
