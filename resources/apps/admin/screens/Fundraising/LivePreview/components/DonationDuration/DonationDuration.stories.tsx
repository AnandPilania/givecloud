import type { Meta, Story } from '@storybook/react'
import { useEffect } from 'react'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'
import { DonationDuration } from './DonationDuration'

export default {
  title: 'Live Preview / Donation Duration',
  component: DonationDuration,
  args: {
    type: 'today_only|monthly',
  },
  argTypes: {
    isOverlayVisible: { control: 'boolean' },
    colour: { control: 'color' },
    type: {
      control: 'radio',
      options: ['today_only|monthly', 'monthly|today_only', 'today_only', 'monthly'],
    },
  },
} as Meta<typeof DonationDuration>

interface Props {
  colour: string
  isOverlayVisible: boolean
  type: string
}

export const Default: Story<typeof DonationDuration & Props> = ({ colour, isOverlayVisible, type }) => {
  useEffect(() => {
    setRootThemeColour({ colour })
  }, [colour])

  return <DonationDuration type={type} isOverlayVisible={isOverlayVisible} className='mb-4' />
}
