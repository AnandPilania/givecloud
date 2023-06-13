import { forwardRef, useState } from 'react'
import { useRecoilState, useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import Input from '@/components/Input/Input'
import useLocalization from '@/hooks/useLocalization'
import bankAccountState from '@/atoms/bankAccount'
import formInputState from '@/atoms/formInput'
import { isEmpty } from '@/utilities/helpers'
import styles from '../AchBankAccountDialog.scss'

const RoutingNumberInput = forwardRef(({ usingHostedPaymentFields }, ref) => {
  const t = useLocalization('screens.pay_with_credit_card')

  const formInput = useRecoilValue(formInputState)
  const [bankAccount, setBankAccount] = useRecoilState(bankAccountState)
  const [shouldValidate, setShouldValidate] = useState(false)

  const isCurrencyCad = formInput.currency_code === 'CAD'
  const usingInput = !usingHostedPaymentFields

  const checkValidity = (value) => {
    setShouldValidate(true)

    if (isEmpty(value)) {
      throw t(isCurrencyCad ? 'routing_number_cad_required' : 'routing_number_required')
    }
  }

  const handleOnChange = (e) => {
    setBankAccount({ ...bankAccount, [e.target.name]: e.target.value })
  }

  if (isCurrencyCad) {
    const isInvalid = shouldValidate && (isEmpty(bankAccount.transit_number) || isEmpty(bankAccount.institution_number))

    return (
      <>
        <div className={styles.routingNumberCAD}>
          <div id='inputPaymentTransitNumber' className={styles.transitNumber} data-private>
            {usingInput && (
              <Input
                ref={ref}
                type='tel'
                name='transit_number'
                placeholder={t('transit_number')}
                defaultValue={bankAccount.transit_number}
                onChange={handleOnChange}
                validator={checkValidity}
                showErrors={false}
                maxLength={5}
                integerOnly
              />
            )}
          </div>

          <div id='inputPaymentInstitutionNumber' className={styles.institutionNumber} data-private>
            {usingInput && (
              <Input
                type='tel'
                name='institution_number'
                placeholder={t('institution_number')}
                defaultValue={bankAccount.institution_number}
                onChange={handleOnChange}
                validator={checkValidity}
                showErrors={false}
                maxLength={3}
                integerOnly
              />
            )}
          </div>
        </div>

        {isInvalid && <span className={styles.errorMessage}>{t('routing_number_cad_required')}</span>}
      </>
    )
  }

  return (
    <div id='inputPaymentRoutingNumber' className={styles.routingNumber} data-private>
      {usingInput && (
        <Input
          ref={ref}
          type='tel'
          name='routing_number'
          placeholder={t('routing_number')}
          defaultValue={bankAccount.routing_number}
          validator={checkValidity}
          onChange={handleOnChange}
          maxLength={9}
          integerOnly
        />
      )}
    </div>
  )
})

RoutingNumberInput.displayName = RoutingNumberInput

RoutingNumberInput.propTypes = {
  usingHostedPaymentFields: PropTypes.bool.isRequired,
}

export default RoutingNumberInput
