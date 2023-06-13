import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Select from '@/fields/Select/Select'

const PersonalInformationAccountTypeSelector = () => {
  const { accountTypes, formErrors } = useContext(StoreContext)
  const error = formErrors.all.account_type

  const onChangeAccountType = (e) => {
    const id = e.target.value
    const chosenAccountType = accountTypes.all.find((accountType) => accountType.id == id)

    accountTypes.set(chosenAccountType)
  }

  return (
    <Label title='Supporter Type' error={error}>
      <Select
        hasError={!!error}
        value={accountTypes.chosen.id}
        width='1/2'
        onChange={onChangeAccountType}
      >
        {accountTypes.all.map((accountType) => (
          <option key={accountType.id} value={accountType.id}>
            {accountType.name}
          </option>
        ))}
      </Select>
    </Label>
  )
}

export default memo(PersonalInformationAccountTypeSelector)
