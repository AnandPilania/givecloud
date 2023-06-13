import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'

const AddressPostalCodeInput = () => {
  const { billing, formErrors } = useContext(StoreContext)
  const value = billing.details.billing_zip
  const label = billing.details.billing_country_code === 'US' ? 'ZIP' : 'Postal'
  const error = formErrors.all.billing_zip

  const onChange = (e) => {
    const value = e.target.value

    billing.setField('billing_zip', value)
  }

  return (
    <Label title={label} error={error}>
      <Input
        hasError={!!error}
        type='text'
        className='w-1/3'
        value={value}
        onChange={onChange}
        required
        autoComplete='postal-code'
        autoCorrect='off'
        spellCheck='off'
      />
    </Label>
  )
}

export default memo(AddressPostalCodeInput)
