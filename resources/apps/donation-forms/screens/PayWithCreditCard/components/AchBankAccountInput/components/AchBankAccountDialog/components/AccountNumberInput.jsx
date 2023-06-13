import { useRecoilState } from 'recoil'
import PropTypes from 'prop-types'
import Input from '@/components/Input/Input'
import useLocalization from '@/hooks/useLocalization'
import bankAccountState from '@/atoms/bankAccount'
import { isEmpty } from '@/utilities/helpers'
import styles from '../AchBankAccountDialog.scss'

const AccountNumberInput = ({ usingHostedPaymentFields }) => {
  const t = useLocalization('screens.pay_with_credit_card')

  const usingInput = !usingHostedPaymentFields
  const [bankAccount, setBankAccount] = useRecoilState(bankAccountState)

  const checkValidity = (value) => {
    if (isEmpty(value)) {
      throw t('account_number_required')
    }
  }

  const handleOnChange = (e) => {
    setBankAccount({ ...bankAccount, account_number: e.target.value })
  }

  return (
    <div id='inputPaymentAccountNumber' className={styles.accountNumber} data-private>
      {usingInput && (
        <Input
          type='tel'
          name='account_number'
          placeholder={t('account_number')}
          defaultValue={bankAccount.account_number}
          validator={checkValidity}
          onChange={handleOnChange}
          maxLength={12}
          integerOnly
        />
      )}
    </div>
  )
}

AccountNumberInput.propTypes = {
  usingHostedPaymentFields: PropTypes.bool.isRequired,
}

export default AccountNumberInput
