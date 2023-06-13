import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'
import Select from '@/fields/Select/Select'

const PersonalInformationTitleInput = () => {
  const { donorTitle, billing, formErrors } = useContext(StoreContext)
  const value = billing.details.billing_title

  const onChangeTitle = (e) => {
    const value = e.target.value

    billing.setField('billing_title', value)
  }

  if (!donorTitle.show) {
    return null
  }

  const error = formErrors.all.billing_title

  return (
    <Label title='Title' error={error}>
      {donorTitle.all.length > 0 ? (
        <Select
          value={value}
          width='1/4'
          onChange={onChangeTitle}
          autoComplete='honorific-prefix'
          required={donorTitle.required}
        >
          <option value=''></option>
          {donorTitle.all.map((title) => (
            <option key={title} value={title}>
              {title}
            </option>
          ))}
        </Select>
      ) : (
        <Input
          hasError={!!error}
          type='text'
          className='w-1/4'
          value={value}
          required={donorTitle.required}
          onChange={onChangeTitle}
          autoComplete='honorific-prefix'
          autoCorrect='off'
          spellcheck='off'
        />
      )}
    </Label>
  )
}

export default memo(PersonalInformationTitleInput)
