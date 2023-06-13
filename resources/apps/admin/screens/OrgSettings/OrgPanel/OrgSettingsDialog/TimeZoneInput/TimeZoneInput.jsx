import { useState } from 'react'
import PropTypes from 'prop-types'
import { useDebounce } from '@/hooks/useDebounce'
import { CommandInput, CommandInputOption } from '@/aerosol'
import { useTimeZonesQuery } from './useTimeZonesQuery'
import { filteredBy } from '@/aerosol/CommandInput'

const TimeZoneInput = ({ selected, setSelected }) => {
  const [isTouched, setIsTouched] = useState(false)
  const { data, isLoading, isError } = useTimeZonesQuery({
    enabled: isTouched,
  })
  const [query, setQuery] = useState('')
  const debounceOnChange = useDebounce(setQuery, 300)
  const filteredData = query === '' ? data : data?.filter((option) => filteredBy(option, query))
  const isQueryEmpty = !filteredData?.length && query !== ''
  const displayValue = (currentValue) => (!currentValue ? '' : currentValue)

  const renderOptions = () => {
    if (isError) return <CommandInputOption value='error'>Something went wrong! try again later</CommandInputOption>
    return filteredData?.map((zone) => (
      <CommandInputOption value={zone} key={zone}>
        {zone}
      </CommandInputOption>
    ))
  }

  return (
    <CommandInput
      isQueryEmpty={isQueryEmpty}
      displayValue={(renderedValue) => displayValue(renderedValue)}
      name='Timezone'
      onChange={debounceOnChange}
      onFocus={() => setIsTouched(true)}
      isLoading={isLoading}
      label='Timezone'
      selected={selected}
      setSelected={setSelected}
    >
      {renderOptions()}
    </CommandInput>
  )
}

TimeZoneInput.propTypes = {
  selected: PropTypes.string.isRequired,
  setSelected: PropTypes.func.isRequired,
}

export { TimeZoneInput }
