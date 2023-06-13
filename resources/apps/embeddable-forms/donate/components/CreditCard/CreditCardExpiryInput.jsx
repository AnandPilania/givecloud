import { memo, useContext } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'
import styles from '@/components/CreditCard/CreditCardExpiryInput.scss'
import inputStyles from '@/fields/Input/Input.scss'

const CreditCardExpiryInput = ({ usingHostedPaymentFields }) => {
  const { payment, formErrors, theme } = useContext(StoreContext)
  const errorMonth = formErrors.all.exp_month
  const errorYear = formErrors.all.exp_year
  const usingInput = !usingHostedPaymentFields

  const onChangeMonth = (e) => {
    const value = e.target.value

    payment.card.set({
      ...payment.card.details,
      exp_month: value,
    })
  }

  const onChangeYear = (e) => {
    const value = e.target.value

    payment.card.set({
      ...payment.card.details,
      exp_year: value,
    })
  }

  const inputPaymentNumberClassNames = classnames(
    usingHostedPaymentFields && [styles.input, 'form-input', inputStyles.root, theme === 'light' && inputStyles.light],
    !usingHostedPaymentFields && styles.inputsContainer
  )

  return (
    <div className={styles.root}>
      <Label title='Expiry' error={errorMonth || errorYear}>
        <div id='inputPaymentExpiry' className={classnames(inputPaymentNumberClassNames)}>
          {usingInput && (
            <>
              <Input
                hasError={!!errorMonth}
                type='tel'
                className='w-14'
                value={payment.card.details.exp_month || ''}
                onChange={onChangeMonth}
                maxLength='2'
                placeholder='MM'
                autoComplete='cc-exp-month'
                autoCorrect='off'
                spellCheck='off'
                autoCapitalize='off'
              />

              <span className={styles.slash}>/</span>

              <Input
                hasError={!!errorYear}
                type='tel'
                className='w-14'
                value={payment.card.details.exp_year || ''}
                onChange={onChangeYear}
                maxLength='2'
                placeholder='YY'
                autoComplete='cc-exp-year'
                autoCorrect='off'
                spellCheck='off'
                autoCapitalize='off'
              />
            </>
          )}
        </div>
      </Label>
    </div>
  )
}

CreditCardExpiryInput.propTypes = {
  usingHostedPaymentFields: PropTypes.bool.isRequired,
}

export default memo(CreditCardExpiryInput)
