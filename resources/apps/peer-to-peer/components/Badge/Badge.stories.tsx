import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { Badge } from './Badge'

export default {
  title: 'Peer to Peer/Badge',
  component: Badge,
} as ComponentMeta<typeof Badge>

const percentages = [0.1, 1, 2, 5, 10, 15, 40]

export const Default: ComponentStory<typeof Badge> = () => {
  return (
    <div className='flex w-full gap-4 flex-wrap'>
      {percentages.map((percentage) => (
        <Badge key={percentage} percentage={percentage} />
      ))}
    </div>
  )
}
