import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import BankAccountHolderTypeInput from '@/components/BankAccount/BankAccountHolderTypeInput'
import BankAccountTypeInput from '@/components/BankAccount/BankAccountTypeInput'
import BankAccountTransitNumberInput from '@/components/BankAccount/BankAccountTransitNumberInput'
import BankAccountInstitutionNumberInput from '@/components/BankAccount/BankAccountInstitutionNumberInput'
import BankAccountRoutingNumberInput from '@/components/BankAccount/BankAccountRoutingNumberInput'
import BankAccountNumberInput from '@/components/BankAccount/BankAccountNumberInput'
import BankAccountAgreeToTermsInput from '@/components/BankAccount/BankAccountAgreeToTermsInput'
import BankAccountSavePaymentMethodInput from '@/components/BankAccount/BankAccountSavePaymentMethodInput'

const BankAccount = () => {
  const { currency } = useContext(StoreContext)

  return (
    <div>
      <BankAccountHolderTypeInput />
      <BankAccountTypeInput />

      {currency.chosen.code === 'CAD' ? (
        <>
          <BankAccountTransitNumberInput />
          <BankAccountInstitutionNumberInput />
        </>
      ) : (
        <BankAccountRoutingNumberInput />
      )}

      <BankAccountNumberInput />
      <BankAccountAgreeToTermsInput />
      <BankAccountSavePaymentMethodInput />
    </div>
  )
}

export default memo(BankAccount)
