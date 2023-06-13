import type { ButtonSizes } from './Button'
import type { ThemeType } from '@/shared/constants/theme'
import type { IconDefinition } from '@fortawesome/pro-regular-svg-icons'
import type { ReactNode } from 'react'
import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { useEffect } from 'react'
import { Button } from './Button'
import { PRIMARY, SECONDARY, LIGHT, INFO, ERROR } from '@/shared/constants/theme'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'

export default {
  title: 'Aerosol/Button',
  component: Button,
  args: {
    isLoading: false,
    isClean: false,
    isOutlined: false,
    isDisabled: false,
    theme: 'primary',
    size: 'medium',
  },
  argTypes: {
    colour: { control: 'color' },
    theme: {
      options: ['primary', 'info', 'error', 'secondary', 'light'],
      control: { type: 'radio' },
    },
    size: {
      options: ['small', 'medium'],
      control: { type: 'radio' },
    },
    isClean: {
      control: 'boolean',
    },
    isOutlined: {
      control: 'boolean',
    },
    isLoading: {
      control: 'boolean',
    },
    isDisabled: {
      control: 'boolean',
    },
    onClick: {
      action: 'click',
    },
    icon: {
      control: false,
    },
    children: {
      control: false,
    },
    className: {
      control: false,
    },
  },
} as ComponentMeta<typeof Button>

const buttomSizes = ['small', 'medium'] as unknown as ButtonSizes[]
const themes = [PRIMARY, SECONDARY, LIGHT, INFO, ERROR] as unknown as ThemeType[]
const icons = ['clock', 'dollar', 'heart'] as unknown as IconDefinition[]

export const Default: ComponentStory<typeof Button> = (args) => (
  <>
    <div className='w-full my-4'>
      {buttomSizes.map((size) => (
        <Button {...args} key={size} className='mr-2' theme={args.theme} size={size}>
          {size}
        </Button>
      ))}
    </div>
    <div className='flex w-full my-4'>
      {themes.map((theme) => (
        <Button key={theme} className='mr-2' {...args} theme={theme}>
          {args.label ? args.label : theme}
        </Button>
      ))}
    </div>
    <div className='flex w-full my-4'>
      {themes.map((theme) => (
        <Button {...args} isOutlined key={theme} className='mr-2' theme={theme}>
          {args.label ? args.label : theme}
        </Button>
      ))}
    </div>
    <div className='flex w-full my-4'>
      {themes.map((theme) => (
        <Button {...args} isClean key={theme} className='mr-2' theme={theme}>
          {args.label ? args.label : theme}
        </Button>
      ))}
    </div>
    <div className='flex w-full my-4'>
      {themes.map((theme) => (
        <Button {...args} isLoading key={theme} className='mr-2' theme={theme}>
          {args.label ? args.label : theme}
        </Button>
      ))}
    </div>
    <div className='w-full my-4'>
      {icons.map((icon, index) => (
        <Button {...args} key={index} className='mr-2' theme={args.theme} icon={icon}>
          {icon as unknown as ReactNode}
        </Button>
      ))}
    </div>
    <div className='w-full my-4'>
      {themes.map((icon) => (
        <Button
          key={icon}
          className='mr-2'
          {...args}
          theme={icon}
          href='www.google.com'
          rel='noreferrer'
          target='_blank'
        >
          Link (A Tag)
        </Button>
      ))}
    </div>
  </>
)

export const CustomTheme = ({ colour }) => {
  useEffect(() => {
    setRootThemeColour({ colour })
  }, [colour])

  return <Button theme='custom'>bark bark bark</Button>
}
