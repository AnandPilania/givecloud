import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'

const PersonalInformationLastNameInput = () => {
  const { billing, formErrors } = useContext(StoreContext)
  const value = billing.details.billing_last_name

  const onChange = (e) => {
    const value = e.target.value

    billing.setField('billing_last_name', value)
  }

  const error = formErrors.all.billing_last_name

  return (
    <Label title='Last Name' error={error}>
      <Input
        hasError={!!error}
        type='text'
        className='w-3/4'
        value={value}
        onChange={onChange}
        autoComplete='family-name'
        autoCorrect='off'
        spellCheck='off'
      />
    </Label>
  )
}

export default memo(PersonalInformationLastNameInput)
