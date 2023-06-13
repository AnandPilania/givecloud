import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Select from '@/fields/Select/Select'
import Input from '@/fields/Input/Input'

const AddressSubdivisionSelector = () => {
  const { billing, formErrors } = useContext(StoreContext)
  const value = billing.details.billing_province_code
  const error = formErrors.all.billing_province_code

  const onChange = (e) => {
    const value = e.target.value
    billing.setField('billing_province_code', value)
  }

  return (
    <Label title={billing.subdivisions.label} error={error}>
      {billing.subdivisions.all && (
        <Select
          required
          hasError={!!error}
          value={value}
          autoComplete='address-level1'
          onChange={onChange}
        >
          <option></option>
          {Object.keys(billing.subdivisions.all).map((code) => (
            <option key={code} value={code}>
              {billing.subdivisions.all[code]}
            </option>
          ))}
        </Select>
      )}

      {!billing.subdivisions.all && (
        <Input
          hasError={!!error}
          type='text'
          required
          autoComplete='address-level1'
          value={value}
          onChange={onChange}
        />
      )}
    </Label>
  )
}

export default memo(AddressSubdivisionSelector)
