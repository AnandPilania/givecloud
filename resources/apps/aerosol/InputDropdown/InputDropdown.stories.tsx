import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { useState } from 'react'
import { InputDropdown } from './InputDropdown'
import { InputDropdownButton } from './InputDropdownButton/InputDropdownButton'
import { DropdownItems, DropdownItem } from '@/aerosol/Dropdown'

export default {
  title: 'Aerosol/Input Dropdown',
  component: InputDropdown,
} as ComponentMeta<typeof InputDropdown>

export const Default: ComponentStory<typeof InputDropdown> = () => {
  const [inputValue, setInputValue] = useState('967-1111')
  const [dropdownValue, setDropdownValue] = useState('CAD +1')

  return (
    <InputDropdown
      isMarginless
      type='tel'
      name='hello'
      inputValue={inputValue}
      dropdownValue={dropdownValue}
      onChange={(e) => setInputValue(e.target.value)}
    >
      <InputDropdownButton>{dropdownValue}</InputDropdownButton>
      <DropdownItems>
        <DropdownItem value='CAD +1' onClick={() => setDropdownValue('CAD +1')} />
        <DropdownItem value='USA +1' onClick={() => setDropdownValue('USA +1')} />
        <DropdownItem value='MEX +321' onClick={() => setDropdownValue('MEX +321')} />
      </DropdownItems>
    </InputDropdown>
  )
}
