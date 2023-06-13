import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'

const BankAccountTransitNumberInput = () => {
  const { payment, formErrors } = useContext(StoreContext)

  const onChange = (e) => {
    const value = e.target.value

    payment.bank.set({
      ...payment.bank.details,
      transit_number: value,
    })
  }

  const error = formErrors.all.transit_number

  return (
    <Label title='Transit Number' error={error}>
      <Input
        hasError={!!error}
        type='tel'
        value={payment.bank.details.transit_number || ''}
        onChange={onChange}
        maxLength='5'
        placeholder='00000'
        autoCorrect='off'
        spellCheck='off'
        autoCapitalize='off'
      />
    </Label>
  )
}

export default memo(BankAccountTransitNumberInput)
