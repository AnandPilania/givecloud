import { memo, useContext } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'
import styles from '@/components/CreditCard/CreditCardCVVInput.scss'
import inputStyles from '@/fields/Input/Input.scss'

const CreditCardCVVInput = ({ usingHostedPaymentFields }) => {
  const { payment, formErrors, theme } = useContext(StoreContext)

  const onChange = (e) => {
    const value = e.target.value

    payment.card.set({
      ...payment.card.details,
      cvv: value,
    })
  }

  const error = formErrors.all.cvv
  const usingInput = !usingHostedPaymentFields

  const inputPaymentNumberClassNames = classnames(
    styles.input,
    'w-20 form-input',
    inputStyles.root,
    theme === 'light' && inputStyles.light
  )

  return (
    <Label title='CVV' error={error}>
      <div id='inputPaymentCVV' className={classnames(usingHostedPaymentFields && inputPaymentNumberClassNames)}>
        {usingInput && (
          <Input
            hasError={!!error}
            type='tel'
            className='w-20'
            value={payment.card.details.cvv || ''}
            onChange={onChange}
            maxLength='4'
            placeholder='000'
            autoComplete='cc-csc'
            autoCorrect='off'
            spellCheck='off'
            autoCapitalize='off'
          />
        )}
      </div>
    </Label>
  )
}

CreditCardCVVInput.propTypes = {
  usingHostedPaymentFields: PropTypes.bool.isRequired,
}

export default memo(CreditCardCVVInput)
