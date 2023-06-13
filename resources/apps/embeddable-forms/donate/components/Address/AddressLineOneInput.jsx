import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'

const AddressLineOneInput = () => {
  const { billing, formErrors } = useContext(StoreContext)
  const value = billing.details.billing_address1

  const onChange = (e) => {
    const value = e.target.value
    billing.setField('billing_address1', value)
  }

  const error = formErrors.all.billing_address1

  return (
    <Label title='Address' error={error}>
      <Input
        hasError={!!error}
        type='text'
        value={value}
        onChange={onChange}
        required
        placeholder='Street and number, P.O. box, c/o.'
        autoComplete='address-line1'
        autoCorrect='off'
        spellCheck='off'
        autoCapitalize='off'
      />
    </Label>
  )
}

export default memo(AddressLineOneInput)
