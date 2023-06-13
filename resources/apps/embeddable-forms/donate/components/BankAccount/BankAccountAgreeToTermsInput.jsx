import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Checkbox from '@/fields/Checkbox/Checkbox'

const BankAccountAgreeToTermsInput = () => {
  const { payment, formErrors } = useContext(StoreContext)

  const onChange = (e) => {
    const value = e.target.checked

    payment.bank.set({
      ...payment.bank.details,
      ach_agree_tos: value,
    })
  }

  const error = formErrors.all.ach_agree_tos

  return (
    <Label error={error}>
      <Checkbox value='1' checked={payment.bank.details.ach_agree_tos} onChange={onChange}>
        By completing this purchase, you authorize us to charge the account above for the amount
        specified in the Total field.
      </Checkbox>
    </Label>
  )
}

export default memo(BankAccountAgreeToTermsInput)
