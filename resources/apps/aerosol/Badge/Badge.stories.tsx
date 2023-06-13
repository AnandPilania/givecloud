import type { ComponentMeta, ComponentStory } from '@storybook/react'
import type { BadgeThemes } from './Badge'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPiggyBank } from '@fortawesome/pro-regular-svg-icons'
import { PRIMARY, WARNING, ERROR, INFO } from '@/shared/constants/theme'
import { Badge } from './Badge'

const themes = [PRIMARY, WARNING, ERROR, INFO, 'gradient'] as BadgeThemes[]

export default {
  title: 'Aerosol/Badge',
  component: Badge,
  args: {
    invertTheme: false,
  },
  argTypes: {
    invertTheme: {
      control: 'boolean',
    },
  },
} as ComponentMeta<typeof Badge>

export const Default: ComponentStory<typeof Badge> = (args) => (
  <div className='w-full flex'>
    {themes.map((theme) => (
      <Badge {...args} key={theme} theme={theme} className='mr-4'>
        <FontAwesomeIcon icon={faPiggyBank} className='mr-2' />
        <span>Themed badge</span>
      </Badge>
    ))}
  </div>
)

export const Inverted: ComponentStory<typeof Badge> = (args) => (
  <div className='w-full flex'>
    {themes.map((theme) => (
      <Badge {...args} key={theme} theme={theme} invertTheme className='mr-4'>
        <FontAwesomeIcon icon={faPiggyBank} className='mr-2' />
        <span>Inverted theme badge</span>
      </Badge>
    ))}
  </div>
)
