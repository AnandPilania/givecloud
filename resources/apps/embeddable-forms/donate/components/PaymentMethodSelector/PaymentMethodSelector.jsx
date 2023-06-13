import { memo, useContext } from 'react'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import { supportedPrimaryColors } from '@/constants/styleConstants'
import styles from '@/components/PaymentMethodSelector/PaymentMethodSelector.scss'

const PaymentMethodSelector = () => {
  const { api, currency, payment, theme, primaryColor } = useContext(StoreContext)
  const isLightTheme = theme === 'light'
  const { bgColor, textColor, bgColorPale } = supportedPrimaryColors[primaryColor] || {}
  const gateways = api.config.gateways
  const methods = []

  if (gateways.credit_card) {
    methods.push({
      key: 'credit_card',
      name: 'Credit / Debit',
    })
  }

  if (gateways.bank_account && api.PaymentTypeGateway('bank_account').canMakeAchPayment(currency.chosen.code)) {
    methods.push({
      key: 'bank_account',
      name: 'Bank',
    })
  }

  if (payment.method.isPaypalAvailable) {
    methods.push({
      key: 'paypal',
      name: 'Paypal',
    })
  }

  if (methods.length === 1) {
    return null
  }

  const onChange = (e) => {
    const value = e.target.value

    payment.method.set(value)
  }

  return (
    <div className={styles.root}>
      {methods.map((method) => {
        const isActive = payment.method.chosen == method.key

        return (
          <label
            key={method.key}
            className={classnames(
              styles.label,
              isLightTheme && styles.light,
              isActive && styles.active,
              isActive && isLightTheme && bgColor,
              isActive && !isLightTheme && `${textColor} ${bgColorPale}`
            )}
          >
            <input
              type='radio'
              className={classnames('form-radio', styles.hiddenInput)}
              onChange={onChange}
              value={method.key}
              checked={method.key === payment.method.chosen ? true : false}
            />

            {method.name}
          </label>
        )
      })}
    </div>
  )
}

export default memo(PaymentMethodSelector)
