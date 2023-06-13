import { Text } from './Text'
import { TEXT_TYPES } from '@/shared/constants/theme'

export default {
  title: 'Aerosol/Text',
  component: Text,
  args: {
    type: 'h1',
    isMarginless: false,
    isBold: false,
    isTruncated: false,
    isSecondaryText: false,
  },
  argTypes: {
    type: {
      control: false,
    },
    isMarginless: {
      control: 'boolean',
    },
    isBold: {
      control: 'boolean',
    },
    isTruncated: {
      control: 'boolean',
    },
    isSecondaryText: {
      control: 'boolean',
    },
  },
}

export const Default = (args) => {
  return (
    <div>
      {TEXT_TYPES.map((type) => (
        <Text {...args} key={type} type={type}>
          <span className='uppercase'>{type}:</span> The quick brown fox jumps over the lazy dog
        </Text>
      ))}
    </div>
  )
}
