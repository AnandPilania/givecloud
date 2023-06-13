import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { faPercent } from '@fortawesome/pro-regular-svg-icons'
import { Box } from '@/aerosol/Box'
import { Columns } from '@/aerosol/Columns'
import { Column } from '@/aerosol/Column'
import { Text } from '@/aerosol/Text'
import { Input } from '@/aerosol/Input'
import { Toggle } from '@/aerosol/Toggle'
import { useState } from '@storybook/addons'

export default {
  title: 'Aerosol/Box',
  component: Box,
  args: {
    isOverflowVisible: false,
    isPaddingless: false,
  },
  argTypes: {
    isOverflowVisible: {
      control: 'boolean',
      description: 'Allows items within the box to overflow the box',
    },
    isPaddingless: {
      control: 'boolean',
      description: 'removes all padding within the box',
    },
  },
} as ComponentMeta<typeof Box>

export const Default: ComponentStory<typeof Box> = ({ isOverflowVisible, isPaddingless }) => {
  return (
    <Box isOverflowVisible={isOverflowVisible} isPaddingless={isPaddingless}>
      Ich bin ein box
    </Box>
  )
}

export const Example = () => {
  const [isEnabled, setIsEnabled] = useState(true)

  return (
    <Box>
      <Columns>
        <Column columnWidth='one'>
          <div className='flex items-center mb-4'>
            <Toggle className='mr-4' isEnabled={isEnabled} setIsEnabled={setIsEnabled} name='page view' />
            <Text isBold isMarginless type='h3'>
              Page View
            </Text>
          </div>
          <Text className='text-gray-600'>Customize your campaign with your organization’s unique brand.</Text>
        </Column>
        <Column columnWidth='two'>
          <form>
            <Input isDisabled={!isEnabled} name='title' label='Page title' />
            <Input isDisabled={!isEnabled} name='description' label='Page Description' />
            <Columns>
              <Column columnWidth='one'>
                <Input isDisabled={!isEnabled} type='number' label='Percentage' name='percentage' icon={faPercent} />
              </Column>
              <Column>
                <Input name='field' isDisabled={!isEnabled} charCountMax={30} label='Field' />
              </Column>
            </Columns>
          </form>
        </Column>
      </Columns>
    </Box>
  )
}
