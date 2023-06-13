import type { Meta, Story } from '@storybook/react'
import { useEffect } from 'react'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'
import { DCCButton } from './DCCButton'

export default {
  title: 'Live Preview / DCC Button',
  component: DCCButton,
  argTypes: {
    colour: { control: 'color' },
  },
} as Meta<typeof DCCButton>

interface Props {
  colour: string
}

export const Default: Story<typeof DCCButton & Props> = ({ colour }) => {
  useEffect(() => {
    setRootThemeColour({ colour })
  }, [colour])

  return <DCCButton />
}
