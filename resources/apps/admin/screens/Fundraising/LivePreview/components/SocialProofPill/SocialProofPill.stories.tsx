import type { Meta, ComponentStory } from '@storybook/react'
import { SocialProofPill } from './SocialProofPill'

export default {
  title: 'Live Preview / Social Proof Pill',
  component: SocialProofPill,
  args: {
    isVisible: true,
    isOverlayVisible: false,
  },
  argTypes: {
    isVisible: { control: 'boolean' },
    isOverlayVisible: { control: 'boolean' },
  },
} as Meta<typeof SocialProofPill>

export const Default: ComponentStory<typeof SocialProofPill> = ({ isOverlayVisible, isVisible }) => {
  return <SocialProofPill isOverlayVisible={isOverlayVisible} isVisible={isVisible} />
}
