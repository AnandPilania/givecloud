import { useRecoilState } from 'recoil'
import useLocalization from '@/hooks/useLocalization'
import bankAccountState from '@/atoms/bankAccount'
import Checkbox from '@/components/Checkbox/Checkbox'
import styles from '../AchBankAccountDialog.scss'

const AccountHolderTypeInput = () => {
  const t = useLocalization('screens.pay_with_credit_card')
  const [bankAccount, setBankAccount] = useRecoilState(bankAccountState)

  const handleOnChange = () => {
    setBankAccount({
      ...bankAccount,
      account_holder_type: bankAccount.account_holder_type === 'personal' ? 'business' : 'personal',
    })
  }

  return (
    <Checkbox
      id='inputBusinessAccount'
      className={styles.businessAccount}
      checked={bankAccount.account_holder_type === 'business'}
      onChange={handleOnChange}
    >
      <span dangerouslySetInnerHTML={t('is_a_business_account_html')} />
    </Checkbox>
  )
}

export default AccountHolderTypeInput
