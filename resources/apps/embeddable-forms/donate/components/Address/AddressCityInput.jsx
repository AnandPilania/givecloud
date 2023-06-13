import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'

const AddressCityInput = () => {
  const { billing, formErrors } = useContext(StoreContext)
  const value = billing.details.billing_city

  const onChange = (e) => {
    const value = e.target.value
    billing.setField('billing_city', value)
  }

  const error = formErrors.all.billing_city

  return (
    <Label title='City' error={error}>
      <Input
        hasError={!!error}
        type='text'
        className='w-1/2'
        value={value}
        onChange={onChange}
        required
        autoComplete='address-level2'
        autoCorrect='off'
        spellCheck='off'
      />
    </Label>
  )
}

export default memo(AddressCityInput)
