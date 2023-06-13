import type { ComponentMeta, ComponentStory } from '@storybook/react'
import type { ThemeType } from '@/shared/constants/theme'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faClock } from '@fortawesome/pro-regular-svg-icons'
import { PRIMARY, SECONDARY, INFO, ERROR, LIGHT } from '@/shared/constants/theme'
import { Tooltip } from './Tooltip'

export default {
  title: 'Aerosol/Tooltip',
  component: Tooltip,
  args: {
    theme: PRIMARY,
    placement: 'top',
    isHidden: false,
    text: '',
    tooltipContent: null,
    isAutoShowing: false,
    isTriggeredOnClick: false,
  },
  argTypes: {
    theme: {
      control: false,
      default: 'primary',
      description: 'primary, secondary, info, error, light',
    },
    placement: {
      options: ['top', 'bottom', 'left', 'right'],
    },
    isHidden: {
      control: 'boolean',
    },
    text: {
      control: 'text',
    },
    tooltipContent: {
      control: false,
    },
    isAutoShowing: {
      control: 'boolean',
    },
    isTriggeredOnClick: {
      control: 'boolean',
    },
  },
} as ComponentMeta<typeof Tooltip>

const themes: ThemeType[] = [PRIMARY, SECONDARY, INFO, ERROR, LIGHT]

export const Default: ComponentStory<typeof Tooltip> = ({ placement, isHidden, isAutoShowing, isTriggeredOnClick }) => {
  const content = (
    <div className='w-40'>
      <div>{new Date().toLocaleTimeString()}</div>
    </div>
  )

  const buttonLabel = isTriggeredOnClick ? 'click me' : 'hover me'

  return (
    <div
      className={`h-96 w-full flex items-center justify-center ${
        placement === 'left' || placement === 'right' ? 'flex-col' : 'flex-row'
      }`}
    >
      {themes.map((theme) => (
        <Tooltip
          key={theme}
          theme={theme}
          isHidden={isHidden}
          placement={placement}
          tooltipContent={content}
          isAutoShowing={isAutoShowing}
          isTriggeredOnClick={isTriggeredOnClick}
        >
          <div className='p-4 border-2 border-black m-2 rounded-lg'>
            <span className='mr-4'>
              {theme} {buttonLabel}
            </span>
            <FontAwesomeIcon icon={faClock} />
          </div>
        </Tooltip>
      ))}
    </div>
  )
}
