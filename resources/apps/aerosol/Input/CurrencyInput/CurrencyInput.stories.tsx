import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { useState } from 'react'
import { CurrencyInput } from '@/aerosol/Input/CurrencyInput'

export default {
  title: 'Aerosol/Currency Input',
  component: CurrencyInput,
  args: {
    name: 'default',
    label: 'Currency Input',
    isLabelHidden: true,
    isChecked: true,
    currency: 'USD',
  },
  argTypes: {
    name: {
      control: 'text',
    },
    label: {
      control: 'text',
    },
    isLabelHidden: {
      control: 'boolean',
    },
    isChecked: {
      control: 'boolean',
    },
  },
} as ComponentMeta<typeof CurrencyInput>

export const Default: ComponentStory<typeof CurrencyInput> = (args) => {
  const [value, setValue] = useState(50)

  const onChange = ({ value }) => {
    setValue(value)
  }

  return (
    <div className='w-28'>
      <CurrencyInput {...args} value={value} onChange={onChange} />
    </div>
  )
}
