import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCopy, faEnvelope, faMessageDots } from '@fortawesome/pro-regular-svg-icons'
import { useState } from 'react'
import { Button } from '@/aerosol/Button'
import { Column } from '@/aerosol/Column'
import { Columns } from '@/aerosol/Columns'
import { Text } from '@/aerosol/Text'
import { Drawer } from './Drawer'

export default {
  title: 'Aerosol/Drawer',
  component: Drawer,
} as ComponentMeta<typeof Drawer>

const containerId = 'layout-portal'

export const Default: ComponentStory<typeof Drawer> = () => {
  const [isOpen, setIsOpen] = useState(false)
  return (
    <>
      <Button onClick={() => setIsOpen(true)}>Open Drawer</Button>
      <Drawer name={containerId} isOpen={isOpen} onClose={() => setIsOpen(false)}>
        <Column columnWidth='six'>
          <Text type='h3'>Invite Teammates</Text>
          <Text type='h5'>1. Share Your Team's Link</Text>
          <Columns isResponsive={false} isStackingOnMobile={false}>
            <Column>
              <Button isOutlined onClick={() => console.log('trigger clipboard copy')} aria-label='copy a short url'>
                gcld.co/lolol2023 <FontAwesomeIcon icon={faCopy} size='lg' className='ml-2' />
              </Button>
            </Column>
            <Column columnWidth='small'>
              <Button isOutlined href={'mailto:?&subject=&body='} aria-label='open an email'>
                <FontAwesomeIcon icon={faEnvelope} size='lg' />
              </Button>
            </Column>
            <Column columnWidth='small'>
              <Button isOutlined href={'sms:?&body='} aria-label='open a text message'>
                <FontAwesomeIcon icon={faMessageDots} size='lg' />
              </Button>
            </Column>
          </Columns>
          <Text type='h5'>2. Share Your Team's Join Code</Text>
          <Text type='h3'>A B C D</Text>
        </Column>
      </Drawer>
    </>
  )
}
