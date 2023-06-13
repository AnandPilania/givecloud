import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Select from '@/fields/Select/Select'

const AddressCountrySelector = () => {
  const { countries, billing, formErrors } = useContext(StoreContext)
  const value = billing.details.billing_country_code

  const onChange = (e) => {
    const value = e.target.value
    billing.setField('billing_country_code', value)
  }

  const error = formErrors.all.billing_country_code

  return (
    <Label title='Country' error={error}>
      <Select hasError={!!error} required value={value} width='2/3' onChange={onChange}>
        <option></option>
        {countries.map((country) => (
          <option key={country.value} value={country.value}>
            {country.label}
          </option>
        ))}
      </Select>
    </Label>
  )
}

export default memo(AddressCountrySelector)
