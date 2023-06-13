import type { Meta, Story } from '@storybook/react'
import { useEffect } from 'react'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'
import { faPlus } from '@fortawesome/pro-light-svg-icons'
import { Icon } from './Icon'

export default {
  title: 'Live Preview / Icon',
  component: Icon,
  args: {
    size: 'default',
  },
  argTypes: {
    colour: { control: 'color' },
  },
} as Meta<typeof Icon>

interface Props {
  colour: string
  size?: 'small' | 'default'
}

export const Default: Story<typeof Icon & Props> = ({ colour, size }) => {
  useEffect(() => {
    setRootThemeColour({ colour })
  }, [colour])

  return <Icon icon={faPlus} className='mr-4' size={size} />
}
