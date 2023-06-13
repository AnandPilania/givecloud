import type { ComponentMeta, ComponentStory } from '@storybook/react'
import type { ChangeEvent } from 'react'
import { useState } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faBolt } from '@fortawesome/pro-regular-svg-icons'
import { Input } from '@/aerosol/Input'
import { Label } from '@/aerosol/Label'
import { faHamburger, faSalad, faAddressCard } from '@fortawesome/pro-regular-svg-icons'

export default {
  title: 'Aerosol/Input',
  component: Input,
  args: {
    charCountMax: 20,
    label: 'Email',
    isOptional: false,
    isDisabled: false,
    isLabelHidden: false,
    isMarginless: false,
    icon: undefined,
    placeholder: 'gimme your email',
    type: 'text',
  },
  argTypes: {
    label: {
      control: 'text',
    },
    charCountMax: {
      control: {
        type: 'number',
        min: 20,
      },
    },
    isOptional: {
      control: 'boolean',
    },
    isLabelHidden: {
      control: 'boolean',
    },
    isDisabled: {
      control: 'boolean',
    },
    isMarginless: {
      control: 'boolean',
    },
    errors: {
      name: 'errors (comma separated array)',
      defaultValue: [],
      control: {
        type: 'object',
      },
    },
    icon: {
      options: [null, faHamburger, faSalad, faAddressCard],
      control: { type: 'radio' },
    },
    name: {
      control: false,
    },
    placeholder: {
      control: 'text',
    },
    type: {
      options: ['text', 'email', 'number'],
      control: { type: 'radio' },
    },
  },
} as ComponentMeta<typeof Input>

export const Default: ComponentStory<typeof Input> = (args) => {
  const [defaultValue, setDefaultValue] = useState('')
  return (
    <Input
      {...args}
      name='default-input'
      value={defaultValue}
      onChange={(e: ChangeEvent<HTMLInputElement>) => setDefaultValue(e.target.value)}
    />
  )
}

export const WithLabel: ComponentStory<typeof Input> = () => {
  return <Input type='email' label='Email' name='with-label' />
}

export const LabelHidden: ComponentStory<typeof Input> = () => {
  return <Input isLabelHidden type='email' label='Email' name='label-hidden' />
}

export const CustomLabel: ComponentStory<typeof Input> = () => {
  return (
    <>
      <Label htmlFor='custom'>
        Custom Label <FontAwesomeIcon icon={faBolt} />
      </Label>
      <Input name='custom' />
    </>
  )
}

export const Optional: ComponentStory<typeof Input> = () => {
  return <Input isOptional type='email' label='Email' name='optional' />
}

export const CharacterCount: ComponentStory<typeof Input> = () => {
  const [value, setValue] = useState('')
  return (
    <Input
      value={value}
      onChange={(e: ChangeEvent<HTMLInputElement>) => setValue(e.target.value)}
      charCountMax={100}
      type='email'
      label='Email'
      name='charcount'
    />
  )
}

export const Errors: ComponentStory<typeof Input> = () => {
  return <Input value='steviewonder.com' errors={['email is not valid']} type='email' label='Email' name='errors' />
}

export const Disabled: ComponentStory<typeof Input> = () => {
  return <Input value='harrysnotter@gmail.com' isDisabled type='email' label='Email' name='disabled' />
}

export const Icon: ComponentStory<typeof Input> = () => {
  return <Input icon={faHamburger} type='text' label='Email' name='icon' />
}

export const AddOn: ComponentStory<typeof Input> = () => {
  return <Input addOn='https://' type='text' label='Website' name='addon' />
}
