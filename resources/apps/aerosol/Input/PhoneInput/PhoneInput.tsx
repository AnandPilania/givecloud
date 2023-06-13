import type { FC, ChangeEvent } from 'react'
import type { InputDropdownProps } from '@/aerosol/InputDropdown'
import { useEffect, useState } from 'react'
import { InputDropdown, InputDropdownButton } from '@/aerosol/InputDropdown'
import { DropdownItems, DropdownItem } from '@/aerosol/Dropdown'
import styles from './PhoneInput.styles.scss'
import codes from './codes.json'

const getCountry = (country: string) =>
  codes.find(
    (code) => code.name.toLowerCase() === country.toLowerCase() || code.code.toLowerCase() === country.toLowerCase()
  ) as Country

interface Country {
  name: string
  flag: string
  code: string
  dialCode: string
}

interface PhoneData {
  country: Country
  phoneNumber: string
}

interface Props extends Omit<InputDropdownProps, 'onChange' | 'children'> {
  country: string
  phoneNumber: string
  onChange: (state: PhoneData) => void
}

const PhoneInput: FC<Props> = ({ country, phoneNumber, onChange, ...rest }) => {
  const [state, setState] = useState<PhoneData>(() => ({
    phoneNumber: phoneNumber ?? '',
    country: getCountry(!!country?.length ? country : 'Canada'),
  }))

  const handleChange = ({ target: { value } }: ChangeEvent<HTMLInputElement>) => {
    setState((prevState) => ({
      ...prevState,
      phoneNumber: prevState.country!.dialCode.concat(value),
    }))
  }

  const handleCountryChange = (countryData: Country) => {
    setState(({ phoneNumber, country }) => ({
      phoneNumber: countryData.dialCode.concat(phoneNumber ? phoneNumber.replace(country.dialCode, '') : ''),
      country: countryData,
    }))
  }

  useEffect(() => {
    onChange(state)
  }, [state])

  const renderCodes = () =>
    codes.map((code) => (
      <DropdownItem aria-label={code.name} key={code.name} value={code.name} onClick={() => handleCountryChange(code)}>
        {code.flag} {code.name} {code.dialCode}
      </DropdownItem>
    ))

  return (
    <InputDropdown
      {...rest}
      inputValue={state.phoneNumber.replace(state.country?.dialCode as string, '')}
      dropdownValue={state.country?.name as string}
      placement='bottom-start'
      type='tel'
      onChange={handleChange}
    >
      <InputDropdownButton aria-label={`selected country: ${state.country?.name} ${state.country?.dialCode}`}>
        {state.country?.dialCode}
      </InputDropdownButton>
      <DropdownItems className={styles.items}>{renderCodes()}</DropdownItems>
    </InputDropdown>
  )
}

export { PhoneInput }
