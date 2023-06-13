import type { Meta, Story } from '@storybook/react'
import { useEffect } from 'react'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'
import { RadioTile } from './RadioTile'

export default {
  title: 'Live Preview / RadioTile',
  component: RadioTile,
  args: {
    isChecked: false,
  },
  argTypes: {
    colour: { control: 'color' },
    isChecked: {
      control: 'boolean',
    },
  },
} as Meta<typeof RadioTile>

interface Props {
  colour: string
  isChecked?: boolean
}

export const Default: Story<typeof RadioTile & Props> = ({ isChecked, colour }) => {
  useEffect(() => {
    setRootThemeColour({ colour })
  }, [colour])

  return (
    <RadioTile isChecked={isChecked} className='mr-2'>
      $95
    </RadioTile>
  )
}
