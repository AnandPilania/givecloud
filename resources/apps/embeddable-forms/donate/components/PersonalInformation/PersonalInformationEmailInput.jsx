import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'

const PersonalInformationEmailInput = () => {
  const { billing, formErrors } = useContext(StoreContext)
  const value = billing.details.billing_email

  const onChange = (e) => {
    const value = e.target.value

    billing.setField('billing_email', value)
  }

  const error = formErrors.all.billing_email

  return (
    <Label title='Email' error={error}>
      <Input
        hasError={!!error}
        type='email'
        className='w-3/4'
        value={value}
        onChange={onChange}
        required
        autoComplete='email'
        autoCorrect='off'
        spellCheck='off'
        autoCapitalize='off'
      />
    </Label>
  )
}

export default memo(PersonalInformationEmailInput)
