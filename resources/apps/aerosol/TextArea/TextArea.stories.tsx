import type { ChangeEvent } from 'react'
import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { useState } from 'react'
import { TextArea } from './TextArea'

export default {
  title: 'Aerosol/TextArea',
  component: TextArea,
  args: {
    rows: 4,
    charCountMax: 20,
    label: 'Email',
    isOptional: false,
    isDisabled: false,
    isLabelHidden: false,
    isMarginless: false,
    placeholder: 'gimme your email',
    type: 'text',
  },
  argTypes: {
    rows: {
      control: 'number',
      min: 4,
    },
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
} as ComponentMeta<typeof TextArea>

export const Default: ComponentStory<typeof TextArea> = (args) => {
  const [value, setValue] = useState('')
  return (
    <TextArea
      {...args}
      value={value}
      onChange={(e: ChangeEvent<HTMLTextAreaElement>) => setValue(e.target.value)}
      id='yolo'
      name='hello'
    />
  )
}

export const Label: ComponentStory<typeof TextArea> = () => {
  const [value, setValue] = useState('')
  return (
    <TextArea
      value={value}
      onChange={(e: ChangeEvent<HTMLTextAreaElement>) => setValue(e.target.value)}
      type='email'
      id='yolo'
      label='Email'
      name='hello'
    />
  )
}

export const LabelHidden: ComponentStory<typeof TextArea> = () => {
  const [value, setValue] = useState('')
  return (
    <TextArea
      charCountMax={500}
      value={value}
      onChange={(e: ChangeEvent<HTMLTextAreaElement>) => setValue(e.target.value)}
      isLabelHidden
      type='email'
      id='yolo'
      label='Email'
      name='hello'
    />
  )
}

export const Optional: ComponentStory<typeof TextArea> = () => {
  const [value, setValue] = useState('')
  return (
    <TextArea
      value={value}
      onChange={(e: ChangeEvent<HTMLTextAreaElement>) => setValue(e.target.value)}
      isOptional
      type='email'
      id='yolo'
      label='Email'
      name='hello'
    />
  )
}

export const AutoGrow: ComponentStory<typeof TextArea> = () => {
  const [value, setValue] = useState('')
  return (
    <TextArea
      value={value}
      onChange={(e: ChangeEvent<HTMLTextAreaElement>) => setValue(e.target.value)}
      isAutoGrowing
      type='email'
      id='yolo'
      label='Email'
      name='hello'
    />
  )
}

export const CharacterCount: ComponentStory<typeof TextArea> = () => {
  const [value, setValue] = useState('')
  return (
    <TextArea
      value={value}
      onChange={(e: ChangeEvent<HTMLTextAreaElement>) => setValue(e.target.value)}
      charCountMax={500}
      type='email'
      id='yolo'
      label='Email'
      name='hello'
    />
  )
}

export const Errors: ComponentStory<typeof TextArea> = () => {
  return (
    <TextArea
      value='steviewonder.com'
      errors={['email is not valid']}
      type='email'
      id='yolo'
      label='Email'
      name='hello'
    />
  )
}

export const Disabled: ComponentStory<typeof TextArea> = () => {
  return <TextArea value='harrysnotter@gmail.com' isDisabled type='email' id='yolo' label='Email' name='hello' />
}
