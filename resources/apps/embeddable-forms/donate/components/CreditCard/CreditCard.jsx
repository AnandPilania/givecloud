import { memo } from 'react'
import CreditCardNumberInput from '@/components/CreditCard/CreditCardNumberInput'
import CreditCardSupportedCards from '@/components/CreditCard/CreditCardSupportedCards'
import CreditCardExpiryInput from '@/components/CreditCard/CreditCardExpiryInput'
import CreditCardCVVInput from '@/components/CreditCard/CreditCardCVVInput'
import CreditCardSavePaymentMethodInput from '@/components/CreditCard/CreditCardSavePaymentMethodInput'
import useHostedCreditCardFields from '@/hooks/useHostedCreditCardFields'

const CreditCard = () => {
  const usingHostedPaymentFields = useHostedCreditCardFields()

  return (
    <>
      <CreditCardNumberInput usingHostedPaymentFields={usingHostedPaymentFields} />
      <CreditCardSupportedCards />
      <CreditCardExpiryInput usingHostedPaymentFields={usingHostedPaymentFields} />
      <CreditCardCVVInput usingHostedPaymentFields={usingHostedPaymentFields} />
      <CreditCardSavePaymentMethodInput />
    </>
  )
}

export default memo(CreditCard)
