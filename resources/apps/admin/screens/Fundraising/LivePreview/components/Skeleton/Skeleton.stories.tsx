import type { Meta, ComponentStory } from '@storybook/react'
import { Skeleton } from './Skeleton'

export default {
  title: 'Live Preview / Skeleton',
  component: Skeleton,
  args: {
    isOverlayVisible: false,
  },
  argTypes: {
    isOverlayVisible: {
      control: 'boolean',
    },
  },
} as Meta<typeof Skeleton>

export const Default: ComponentStory<typeof Skeleton> = (args) => <Skeleton {...args} />
