import type { ComponentMeta, ComponentStory } from '@storybook/react'
import type { ChangeEvent } from 'react'
import { useState } from 'react'
import { Checkbox } from './Checkbox'
import { CheckboxGroup } from './CheckboxGroup'
import { Text } from '@/aerosol/Text'

export default {
  title: 'Aerosol/CheckboxGroup',
  component: CheckboxGroup,
  args: {
    isDisabled: false,
    isLabelVisible: true,
  },
  argTypes: {
    isDisabled: { control: 'boolean' },
    isLabelVisible: { control: 'boolean' },
  },
} as ComponentMeta<typeof CheckboxGroup>

export const Default: ComponentStory<typeof CheckboxGroup> = ({ isDisabled, isLabelVisible }) => {
  const [values, setValues] = useState({ en_US: true, es_MX: false, fr_CA: false })

  const handleOnChange = ({ target }: ChangeEvent<HTMLInputElement>) => {
    const { value } = target
    setValues({ ...values, [value]: !values[value] })
  }

  return (
    <CheckboxGroup
      name='locales'
      label='Locales'
      values={values}
      onChange={handleOnChange}
      isDisabled={isDisabled}
      isLabelVisible={isLabelVisible}
    >
      <Checkbox id='english' value='en_US' disabled>
        <Text type='h5' isMarginless>
          English (default)
        </Text>
      </Checkbox>
      <Checkbox id='french' value='fr_CA'>
        <Text type='h5' isMarginless>
          French
        </Text>
      </Checkbox>
      <Checkbox id='spanish' value='es_MX'>
        <Text type='h5' isMarginless>
          Spanish
        </Text>
      </Checkbox>
    </CheckboxGroup>
  )
}
