import { memo, useContext } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import CreditCardLogo from '@/components/CreditCard/CreditCardLogo'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'
import styles from '@/components/CreditCard/CreditCardNumberInput.scss'
import inputStyles from '@/fields/Input/Input.scss'

const Givecloud = window.Givecloud

const CreditCardNumberInput = ({ usingHostedPaymentFields }) => {
  const { payment, formErrors, theme } = useContext(StoreContext)

  const onChange = (e) => {
    const value = e.target.value
    const type = Givecloud.CardholderData.getNumberType(value)

    payment.card.set({
      ...payment.card.details,
      number: value,
      type,
    })
  }

  const error = formErrors.all.number
  const usingInput = !usingHostedPaymentFields

  const inputPaymentNumberClassNames = classnames(
    styles.input,
    'w-60 h-[26px] form-input',
    inputStyles.root,
    theme === 'light' && inputStyles.light
  )

  return (
    <div className={styles.root}>
      <Label title='Number' error={error}>
        <CreditCardLogo />

        <div id='inputPaymentNumber' className={classnames(usingHostedPaymentFields && inputPaymentNumberClassNames)}>
          {usingInput && (
            <Input
              hasError={!!error}
              type='text'
              className={`${styles.input} w-60`}
              mask='9999 9999 9999 9999'
              value={payment.card.details.number || ''}
              onChange={onChange}
              placeholder='0000 0000 0000 0000'
              autoComplete='cc-number'
              autoCorrect='off'
              spellCheck='off'
              autoCapitalize='off'
            />
          )}
        </div>
      </Label>
    </div>
  )
}

CreditCardNumberInput.propTypes = {
  usingHostedPaymentFields: PropTypes.bool.isRequired,
}

export default memo(CreditCardNumberInput)
