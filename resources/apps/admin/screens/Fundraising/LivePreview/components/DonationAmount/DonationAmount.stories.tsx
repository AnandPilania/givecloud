import type { Meta, Story } from '@storybook/react'
import { useEffect } from 'react'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'
import { DonationAmount } from './DonationAmount'

export default {
  title: 'Live Preview / Donation Amount',
  component: DonationAmount,
  args: {
    isOverlayVisible: false,
  },
  argTypes: {
    colour: { control: 'color' },
    isOverlayVisible: { control: 'boolean' },
  },
} as Meta<typeof DonationAmount>

interface Props {
  colour: string
  isOverlayVisible: boolean
}

export const Default: Story<typeof DonationAmount & Props> = ({ colour, isOverlayVisible }) => {
  useEffect(() => {
    setRootThemeColour({ colour })
  }, [colour])

  return <DonationAmount isOverlayVisible={isOverlayVisible} className='mb-8' />
}
