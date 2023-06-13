import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Select from '@/fields/Select/Select'

const BankAccountHolderTypeInput = () => {
  const { payment, formErrors } = useContext(StoreContext)

  const onChange = (e) => {
    const value = e.target.value

    payment.bank.set({
      ...payment.bank.details,
      account_holder_type: value,
    })
  }

  const error = formErrors.all.account_holder_type

  return (
    <Label title='Account Holder Type' error={error}>
      <Select
        hasError={!!error}
        required
        width='1/2'
        value={payment.bank.details.account_holder_type}
        onChange={onChange}
        autoComplete='off'
      >
        <option></option>
        <option value='personal'>Individual</option>
        <option value='business'>Company</option>
      </Select>
    </Label>
  )
}

export default memo(BankAccountHolderTypeInput)
