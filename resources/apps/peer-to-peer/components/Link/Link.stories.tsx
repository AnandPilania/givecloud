import type { Meta, Story } from '@storybook/react'
import { useEffect } from 'react'
import { BrowserRouter } from 'react-router-dom'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/pro-regular-svg-icons'
import { Text } from '../Text'
import { Link } from './Link'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'

export default {
  title: 'Peer to Peer / Link',
  component: Link,
  argTypes: {
    colour: { control: 'color' },
  },
} as Meta<typeof Link>

interface CustomColour {
  colour: string
}

export const Default: Story<typeof Link & CustomColour> = ({ colour }) => {
  useEffect(() => setRootThemeColour({ colour }), [colour])

  return (
    <BrowserRouter>
      <Link to='/'>
        <Text>
          givecloud.com <FontAwesomeIcon icon={faArrowRight} size='sm' />
        </Text>
      </Link>
    </BrowserRouter>
  )
}
