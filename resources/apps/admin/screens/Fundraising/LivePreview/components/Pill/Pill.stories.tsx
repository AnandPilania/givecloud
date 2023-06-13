import type { Meta, Story } from '@storybook/react'
import { useEffect } from 'react'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheck } from '@fortawesome/free-solid-svg-icons'
import { Pill } from './Pill'

export default {
  title: 'Live Preview / Pill',
  component: Pill,
  args: {
    isInverted: false,
  },
  argTypes: {
    colour: { control: 'color' },
    isInverted: { control: 'boolean' },
  },
} as Meta<typeof Pill>

interface Props {
  colour: string
  isInverted?: boolean
}

export const Default: Story<typeof Pill & Props> = ({ colour, isInverted }) => {
  useEffect(() => {
    setRootThemeColour({ colour })
  }, [colour])

  return (
    <div className='max-w-[6rem]'>
      <Pill isInverted={isInverted}>
        <FontAwesomeIcon icon={faCheck} className='mr-1 mb-0.5' />
        Monthly
      </Pill>
    </div>
  )
}
