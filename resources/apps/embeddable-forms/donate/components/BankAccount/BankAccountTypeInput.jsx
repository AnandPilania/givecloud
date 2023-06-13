import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Select from '@/fields/Select/Select'

const BankAccountTypeInput = () => {
  const { payment, formErrors } = useContext(StoreContext)

  const onChange = (e) => {
    const value = e.target.value

    payment.bank.set({
      ...payment.bank.details,
      account_type: value,
    })
  }

  const error = formErrors.all.account_type

  return (
    <Label title='Account Type' error={error}>
      <Select
        hasError={!!error}
        required
        width='1/2'
        value={payment.bank.details.account_type}
        onChange={onChange}
        autoComplete='off'
      >
        <option></option>
        <option value='checking'>Checking</option>
        <option value='savings'>Savings</option>
      </Select>
    </Label>
  )
}

export default memo(BankAccountTypeInput)
