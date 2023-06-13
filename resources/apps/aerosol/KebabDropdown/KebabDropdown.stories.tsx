import { ComponentMeta, ComponentStory } from '@storybook/react'
import { KebabDropdown } from './KebabDropdown'
import { KebabDropdownItem } from './KebabDropdownItem'

export default {
  title: 'Aerosol/Kebab Dropdown',
  component: KebabDropdown,
  args: {
    placement: 'left',
  },
  argTypes: {
    placement: {
      options: ['left', 'right'],
      control: { type: 'radio' },
    },
  },
} as ComponentMeta<typeof KebabDropdown>

export const Default: ComponentStory<typeof KebabDropdown> = ({ placement }) => {
  return (
    <div className='w-full flex justify-center'>
      <KebabDropdown placement={placement}>
        <KebabDropdownItem>Kebab kebab</KebabDropdownItem>
        <KebabDropdownItem href='/google.com'>We have a Shawarma too</KebabDropdownItem>
        <KebabDropdownItem onClick={() => console.log('click')}>No Kebab for you</KebabDropdownItem>
      </KebabDropdown>
    </div>
  )
}
