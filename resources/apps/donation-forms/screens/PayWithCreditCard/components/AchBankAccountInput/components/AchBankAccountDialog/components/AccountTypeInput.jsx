import { useRecoilState } from 'recoil'
import RadioButton from '@/components/RadioButton/RadioButton'
import useLocalization from '@/hooks/useLocalization'
import bankAccountState from '@/atoms/bankAccount'
import styles from '../AchBankAccountDialog.scss'

const AccountTypeInput = () => {
  const t = useLocalization('screens.pay_with_credit_card')
  const [bankAccount, setBankAccount] = useRecoilState(bankAccountState)

  const handleOnChange = (e) => {
    setBankAccount({ ...bankAccount, account_type: e.target.value })
  }

  return (
    <fieldset>
      <legend className='sr-only'>{t('account_type')}</legend>
      <div className={styles.accountType}>
        <RadioButton
          id='inputAccountTypeChecking'
          name='account_type'
          value='checking'
          onChange={handleOnChange}
          checked={bankAccount.account_type === 'checking'}
        >
          {t('checking')}
        </RadioButton>

        <RadioButton
          id='inputAccountTypeSavings'
          name='account_type'
          value='savings'
          onChange={handleOnChange}
          checked={bankAccount.account_type === 'savings'}
        >
          {t('savings')}
        </RadioButton>
      </div>
    </fieldset>
  )
}

export default AccountTypeInput
