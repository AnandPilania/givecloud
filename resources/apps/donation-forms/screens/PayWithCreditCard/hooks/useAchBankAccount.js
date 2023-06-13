import { useRecoilValue } from 'recoil'
import formInputState from '@/atoms/formInput'
import bankAccountState from '@/atoms/bankAccount'

const useAchBankAccount = () => {
  const formInput = useRecoilValue(formInputState)
  const bankAccount = useRecoilValue(bankAccountState)

  const isCurrencyCad = formInput.currency_code === 'CAD'
  const usingAchBankAccount = formInput.payment_type === 'bank_account'

  // prettier-ignore
  const hasInvalidAchBankAccount =
    !bankAccount.mandate_accepted ||
    !bankAccount.account_number ||
    (isCurrencyCad
      ? !bankAccount.transit_number || !bankAccount.institution_number
      : !bankAccount.routing_number
    )

  return { usingAchBankAccount, hasInvalidAchBankAccount }
}

export default useAchBankAccount
