import type { FC } from 'react'
import type { CommandInputProps } from '@/aerosol/CommandInput'
import type { SelectedType } from '@/aerosol/CommandInput'
import { useState, useMemo } from 'react'
import { CommandInput, CommandInputOption } from '@/aerosol'
import { filteredByObjKey, filteredByObjValue } from '@/aerosol/CommandInput'
import { useDebounce } from '@/hooks/useDebounce'
import { useLegalCountryQuery } from './useLegalCountryQuery'

export interface Country {
  [key: string]: string
}

interface Props extends Pick<CommandInputProps<string>, 'value'> {
  setSelected: (country: SelectedType<Country>) => void
  selected?: string
}

const LegalCountryCommandInput: FC<Props> = ({ setSelected, selected }) => {
  const [isQueryEnabled, setIsQueryEnabled] = useState(false)
  const { data, isLoading, isError } = useLegalCountryQuery({ enabled: isQueryEnabled })
  const [query, setQuery] = useState('')
  const debounceOnChange = useDebounce(setQuery, 300)

  const filteredCountries: Country = useMemo(
    () => ({
      ...filteredByObjKey({ query, data }),
      ...filteredByObjValue({ query, data }),
    }),
    [query, data]
  )

  const countries = query === '' ? data ?? {} : filteredCountries
  const isQueryEmpty = !Object.keys(countries).length

  const renderOptions = () => {
    if (isError)
      return <CommandInputOption value='error'>There was an error. Please try again later.</CommandInputOption>

    return countries
      ? Object.entries(countries).map(([countryCode, countryName]) => (
          <CommandInputOption key={countryName} value={countryCode}>
            {countryName}
          </CommandInputOption>
        ))
      : null
  }

  const displayValue = (value: SelectedType<Country>) => {
    if (typeof value === 'string') return value
    return ''
  }

  return (
    <CommandInput<Country>
      data-testid='legal-country-command-input'
      setSelected={setSelected}
      selected={selected}
      name='orgLegalCountry'
      query={query}
      isQueryEmpty={isQueryEmpty}
      onChange={debounceOnChange}
      displayValue={displayValue}
      onFocus={() => setIsQueryEnabled(true)}
      isLoading={isLoading}
    >
      {renderOptions()}
    </CommandInput>
  )
}

export { LegalCountryCommandInput }
