import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'

const BankAccountInstitutionNumberInput = () => {
  const { payment, formErrors } = useContext(StoreContext)

  const onChange = (e) => {
    const value = e.target.value

    payment.bank.set({
      ...payment.bank.details,
      institution_number: value,
    })
  }

  const error = formErrors.all.institution_number

  return (
    <Label title='Institution Number' error={error}>
      <Input
        hasError={!!error}
        type='tel'
        value={payment.bank.details.institution_number || ''}
        onChange={onChange}
        maxLength='3'
        placeholder='000'
        autoCorrect='off'
        spellCheck='off'
        autoCapitalize='off'
      />
    </Label>
  )
}

export default memo(BankAccountInstitutionNumberInput)
