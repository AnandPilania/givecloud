import type { FC } from 'react'
import type { DrawerProps } from '@/aerosol'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCommentDots, faEnvelope, faLink } from '@fortawesome/pro-regular-svg-icons'
import { faFacebookF, faLinkedin, faTwitter } from '@fortawesome/free-brands-svg-icons'
import { Column, Columns, Drawer } from '@/aerosol'
import { Text } from '@/components'
import styles from './JoinShareDrawer.styles.scss'

const dummyShareLinks = {
  facebook: 'https://www.facebook.com',
  twitter: 'https://www.twitter.com',
  linkedIn: 'https://www.linkedin.com',
  sms: 'sms:?&body=https://www.google.com',
  email: 'mailto:?&subject=sos need cash money',
  link: 'probably gonna trigger copy to clipboard',
}

const socialIcons = {
  facebook: faFacebookF,
  twitter: faTwitter,
  linkedIn: faLinkedin,
  sms: faCommentDots,
  email: faEnvelope,
  link: faLink,
}

type Props = Pick<DrawerProps, 'isOpen' | 'onClose'>

const JoinShareDrawer: FC<Props> = ({ isOpen, onClose }) => {
  const renderShareLinks = () =>
    Object.keys(dummyShareLinks).map((platform) => (
      <Column key={platform} columnWidth='small'>
        <a
          href={dummyShareLinks[platform]}
          target='_blank'
          rel='noreferrer'
          aria-label={`share your fundraiser using ${platform}`}
          className={styles.platformLink}
        >
          <FontAwesomeIcon icon={socialIcons[platform]} aria-hidden='true' />
        </a>
      </Column>
    ))

  return (
    <Drawer name='share fundraiser' isOpen={isOpen} onClose={onClose}>
      <Column columnWidth='six' className={styles.column}>
        <Text type='h3'>Share Your Fundraiser</Text>
        <Columns className={styles.columns} isWrapping isResponsive={false} isStackingOnMobile={false}>
          {renderShareLinks()}
        </Columns>
      </Column>
    </Drawer>
  )
}

export { JoinShareDrawer }
