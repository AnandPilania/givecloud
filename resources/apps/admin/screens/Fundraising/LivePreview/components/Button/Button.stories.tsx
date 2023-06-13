import type { Meta, Story } from '@storybook/react'
import { useEffect } from 'react'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'
import { Button } from './Button'

export default {
  title: 'Live Preview / Button',
  component: Button,
  argTypes: {
    colour: { control: 'color' },
    isOutlined: { control: 'boolean' },
  },
} as Meta<typeof Button>

interface Props {
  colour: string
  isOutlined: boolean
}

export const Default: Story<typeof Button & Props> = ({ colour, isOutlined }) => {
  useEffect(() => {
    setRootThemeColour({ colour })
  }, [colour])

  return <Button isOutlined={isOutlined}>I am a dummy</Button>
}
