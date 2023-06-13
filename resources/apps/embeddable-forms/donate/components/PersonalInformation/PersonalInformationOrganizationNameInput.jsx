import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'

const PersonalInformationOrganizationNameInput = () => {
  const { accountTypes, billing, formErrors } = useContext(StoreContext)
  const value = billing.details.billing_company

  if (!accountTypes.chosen.is_organization) {
    return null
  }

  const onChange = (e) => {
    const value = e.target.value

    billing.setField('billing_company', value)
  }

  const error = formErrors.all.billing_company

  return (
    <Label title='Organization' error={error}>
      <Input
        hasError={!!error}
        type='text'
        value={value}
        onChange={onChange}
        autoComplete='organization'
        autoCorrect='off'
        spellCheck='off'
      />
    </Label>
  )
}

export default memo(PersonalInformationOrganizationNameInput)
