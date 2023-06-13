import type { FC } from 'react'
import type { DrawerProps } from '@/aerosol'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faEnvelope, faCopy, faMessageDots } from '@fortawesome/pro-regular-svg-icons'
import { Button, Column, Columns, Drawer, triggerToast } from '@/aerosol'
import { Text } from '@/components'

const addSpacesToCode = (code: string) => code?.split('')?.join(' ')
interface Props extends Pick<DrawerProps, 'isOpen' | 'onClose'> {
  joinCode: string
  name: string
  shortUrl: string
}

const InviteDrawer: FC<Props> = ({ isOpen, onClose, joinCode, shortUrl, name }) => {
  const handleCopyUrl = () => {
    navigator.clipboard.writeText(shortUrl)
    triggerToast({ type: 'success', header: 'Url successfully copied to clipboard!' })
  }

  return (
    <Drawer name='invite teammates' isOpen={isOpen} onClose={onClose}>
      <Text type='h4'>Invite Teammates</Text>
      <Columns isMarginless>
        <Column>
          <Text>1. Share Your Team's Link</Text>
          <Text isSecondaryColour isMarginless>
            {shortUrl}
          </Text>
        </Column>
      </Columns>
      <Columns isMarginless isResponsive={false} className='justify-center'>
        <Column columnWidth='small'>
          <Button
            isOutlined
            onClick={() => handleCopyUrl()}
            aria-label='copy a short url to invite your teammates to join'
            theme='custom'
          >
            <FontAwesomeIcon icon={faCopy} size='lg' className='ml-2' />
          </Button>
        </Column>
        <Column columnWidth='small'>
          <Button
            isOutlined
            href={`mailto:?&subject=Join ${name}&body=Follow this link to join ${name}: ${shortUrl} (Code: ${joinCode})`}
            aria-label='open an email to invite your teammates to join'
            theme='custom'
          >
            <FontAwesomeIcon icon={faEnvelope} size='lg' />
          </Button>
        </Column>
        <Column columnWidth='small'>
          <Button
            isOutlined
            href={`sms:?&body=Follow this link to join ${name}: ${shortUrl} (Code: ${joinCode})`}
            aria-label='open a text message with the short url and join code to your teammates'
            theme='custom'
          >
            <FontAwesomeIcon icon={faMessageDots} size='lg' />
          </Button>
        </Column>
      </Columns>
      <Columns isMarginless>
        <Column>
          <Text isMarginless>2. Share Your Team's Join Code</Text>
        </Column>
      </Columns>
      <span className='text-4xl'>{addSpacesToCode(joinCode)}</span>
    </Drawer>
  )
}

export { InviteDrawer }
