import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'

const AddressLineTwoInput = () => {
  const { billing, formErrors } = useContext(StoreContext)
  const value = billing.details.billing_address2

  const onChange = (e) => {
    const value = e.target.value
    billing.setField('billing_address2', value)
  }

  const error = formErrors.all.billing_address2

  return (
    <Label title='Address 2' error={error}>
      <Input
        hasError={!!error}
        type='text'
        value={value}
        onChange={onChange}
        required
        placeholder='Apartment, suite, unit, building, floor, etc.'
        autoComplete='address-line2'
        autoCorrect='off'
        spellCheck='off'
        autoCapitalize='off'
      />
    </Label>
  )
}

export default memo(AddressLineTwoInput)
