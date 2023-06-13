import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Checkbox from '@/fields/Checkbox/Checkbox'

const CreditCardSavePaymentMethodInput = () => {
  const { variants, payment } = useContext(StoreContext)

  const onChange = (e) => {
    const value = e.target.checked

    payment.card.set({
      ...payment.card.details,
      save_payment_method: value,
    })
  }

  if (variants.chosen.billing_period !== 'onetime') {
    return null
  }

  return (
    <Label>
      <Checkbox value='1' checked={payment.card.details.save_payment_method} onChange={onChange}>
        Save this payment information securely.
      </Checkbox>
    </Label>
  )
}

export default memo(CreditCardSavePaymentMethodInput)
