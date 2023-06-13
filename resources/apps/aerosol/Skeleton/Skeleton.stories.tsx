import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { Skeleton } from './Skeleton'

export default {
  title: 'Aerosol/Skeleton',
  component: Skeleton,
  args: {
    width: 'small',
    height: 'small',
  },
  argTypes: {
    width: {
      control: { type: 'select' },
      default: 'small',
      options: ['small', 'medium', 'large', 'full'],
    },
    height: {
      control: { type: 'select' },
      default: 'small',
      options: ['small', 'medium', 'large', 'full'],
    },
  },
} as ComponentMeta<typeof Skeleton>

export const Default: ComponentStory<typeof Skeleton> = ({ ...args }) => {
  return (
    <div className='w-full flex justify-center items-start h-96 flex-col'>
      <Skeleton {...args} />
    </div>
  )
}
