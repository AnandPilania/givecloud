import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'

const PersonalInformationFirstNameInput = () => {
  const { billing, formErrors } = useContext(StoreContext)
  const value = billing.details.billing_first_name

  const onChange = (e) => {
    const value = e.target.value

    billing.setField('billing_first_name', value)
  }

  const error = formErrors.all.billing_first_name

  return (
    <Label title='First Name' error={error}>
      <Input
        hasError={!!error}
        type='text'
        className='w-3/4'
        value={value}
        onChange={onChange}
        autoComplete='given-name'
        autoCorrect='off'
        spellCheck='off'
      />
    </Label>
  )
}

export default memo(PersonalInformationFirstNameInput)
