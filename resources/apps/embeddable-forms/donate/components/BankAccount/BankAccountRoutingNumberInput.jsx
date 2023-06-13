import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'

const BankAccountRoutingNumberInput = () => {
  const { payment, formErrors } = useContext(StoreContext)

  const onChange = (e) => {
    const value = e.target.value

    payment.bank.set({
      ...payment.bank.details,
      routing_number: value,
    })
  }

  const error = formErrors.all.routing_number

  return (
    <Label title='Routing Number' error={error}>
      <Input
        hasError={!!error}
        type='number'
        value={payment.bank.details.routing_number || ''}
        onChange={onChange}
        placeholder='0000000'
        autoCorrect='off'
        spellCheck='off'
        autoCapitalize='off'
      />
    </Label>
  )
}

export default memo(BankAccountRoutingNumberInput)
