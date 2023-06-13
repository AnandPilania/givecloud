import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faVideo } from '@fortawesome/pro-regular-svg-icons'
import { ERROR, INFO, PRIMARY, ThemeType, WARNING } from '@/shared/constants/theme'
import { Chip } from './Chip'

const themes = [PRIMARY, WARNING, ERROR, INFO] as ThemeType[]

export default {
  title: 'Aerosol/Chip',
  component: Chip,
  args: {
    invertTheme: false,
  },
  argTypes: {
    invertTheme: {
      control: 'boolean',
    },
  },
} as ComponentMeta<typeof Chip>

export const Default: ComponentStory<typeof Chip> = (args) => (
  <div className='w-full flex'>
    {themes.map((theme) => (
      <Chip {...args} key={theme} theme={theme} className='mr-4' onClick={() => {}}>
        <span className='mr-2'>{theme}</span>
        <FontAwesomeIcon icon={faVideo} />
      </Chip>
    ))}
  </div>
)

export const SentenceCase = () => (
  <div className='w-full flex'>
    {themes.map((theme) => (
      <Chip key={theme} theme={theme} className='mr-4'>
        <span className='capitalize font-medium tracking-wider mr-2'>{theme}</span>
        <FontAwesomeIcon icon={faVideo} />
      </Chip>
    ))}
  </div>
)
