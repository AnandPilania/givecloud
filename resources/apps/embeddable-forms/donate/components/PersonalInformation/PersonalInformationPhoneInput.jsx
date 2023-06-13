import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'

const PersonalInformationPhoneInput = () => {
  const { billing, formErrors } = useContext(StoreContext)
  const value = billing.details.billing_phone

  const onChange = (e) => {
    const value = e.target.value

    billing.setField('billing_phone', value)
  }

  const error = formErrors.all.billing_phone

  return (
    <Label title='Phone' error={error}>
      <Input
        hasError={!!error}
        type='tel'
        className='w-1/2'
        value={value}
        onChange={onChange}
        autoComplete='tel'
        autoCorrect='off'
        spellCheck='off'
      />
    </Label>
  )
}

export default memo(PersonalInformationPhoneInput)
