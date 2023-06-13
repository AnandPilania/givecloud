import type { FC } from 'react'
import type { CommandInputProps, SelectedType } from '@/aerosol/CommandInput/CommandInput'
import { useState } from 'react'
import { useDebounce } from '@/hooks/useDebounce'
import { CommandInput, CommandInputOption } from '@/aerosol'
import { filteredBy } from '@/aerosol/CommandInput'

export interface DPCode {
  code: string
  description: string
}

interface Props extends Pick<CommandInputProps<DPCode>, 'name' | 'value' | 'onFocus' | 'isLoading'> {
  data?: DPCode[]
  label: string
  setSelected: (name: string, option: SelectedType<DPCode>) => void
}

const DonorPerfectCommandInput: FC<Props> = ({ data, name, setSelected, value, ...rest }) => {
  const [query, setQuery] = useState('')
  const selectedOption = data?.find((option) => option.code === value)
  const debounceOnChange = useDebounce(setQuery, 300)
  const filteredData = query === '' ? data : data?.filter((option) => filteredBy(option.code, query))
  const isQueryEmpty = !filteredData?.length

  const displayValue = (value: SelectedType<DPCode>) => {
    if (typeof value === 'object') return value?.code
    return value ?? ''
  }

  return (
    <CommandInput<DPCode>
      {...rest}
      isQueryEmpty={isQueryEmpty}
      selected={selectedOption}
      name={name}
      defaultValue={displayValue(value)}
      displayValue={displayValue}
      setSelected={(value) => setSelected(name, value)}
      onChange={debounceOnChange}
    >
      {filteredData?.map((option) => (
        <CommandInputOption key={option.code} value={option}>
          {option.code}
        </CommandInputOption>
      ))}
    </CommandInput>
  )
}

export { DonorPerfectCommandInput }
