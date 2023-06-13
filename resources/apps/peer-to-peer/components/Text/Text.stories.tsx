import type { Meta, Story } from '@storybook/react'
import { useEffect } from 'react'
import { Text } from './Text'
import { TEXT_TYPES } from '@/shared/constants/theme'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'

export default {
  title: 'Peer to Peer/Text',
  component: Text,
  args: {
    type: 'h1',
    isMarginless: false,
    isTruncated: false,
    isSecondaryColour: false,
    isAccentColour: false,
  },
  argTypes: {
    type: {
      control: false,
    },
    isMarginless: {
      control: 'boolean',
    },
    isTruncated: {
      control: 'boolean',
    },
    isSecondaryColour: {
      control: 'boolean',
    },
    isAccentColour: {
      control: 'boolean',
    },
    colour: { control: 'color' },
  },
} as Meta<typeof Text>

interface CustomColour {
  colour: string
}

export const Default: Story<typeof Text & CustomColour> = (args) => {
  const p2pTextTypes = TEXT_TYPES.filter((type) => type !== 'footnote' && type !== 'h5')

  return (
    <div>
      {p2pTextTypes.map((type) => (
        <Text {...args} key={type} type={type}>
          <span className='uppercase'>{type}:</span> The quick brown fox jumps over the lazy dog
        </Text>
      ))}
    </div>
  )
}

export const WithCustomColour: Story<typeof Text & CustomColour> = ({ colour, ...args }) => {
  const p2pTextTypes = TEXT_TYPES.filter((type) => type !== 'h5')

  useEffect(() => setRootThemeColour({ colour }), [colour])

  return (
    <div>
      {p2pTextTypes.map((type) => (
        <Text {...args} key={type} type={type} theme='custom'>
          <span className='uppercase'>{type}:</span> The quick brown fox jumps over the lazy dog
        </Text>
      ))}
    </div>
  )
}
