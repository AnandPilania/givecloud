import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'

const BankAccountNumberInput = () => {
  const { payment, formErrors } = useContext(StoreContext)

  const onChange = (e) => {
    const value = e.target.value

    payment.bank.set({
      ...payment.bank.details,
      account_number: value,
    })
  }

  const error = formErrors.all.account_number

  return (
    <Label title='Account Number' error={error}>
      <Input
        hasError={!!error}
        type='number'
        value={payment.bank.details.account_number || ''}
        onChange={onChange}
        minLength='4'
        maxLength='17'
        placeholder='0000000'
        autoCorrect='off'
        spellCheck='off'
        autoCapitalize='off'
      />
    </Label>
  )
}

export default memo(BankAccountNumberInput)
