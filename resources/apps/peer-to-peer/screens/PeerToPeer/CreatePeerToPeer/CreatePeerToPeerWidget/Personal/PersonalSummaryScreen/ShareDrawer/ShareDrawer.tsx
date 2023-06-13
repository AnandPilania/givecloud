import type { FC } from 'react'
import type { DrawerProps } from '@/aerosol'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCommentDots, faEnvelope, faLink } from '@fortawesome/pro-regular-svg-icons'
import { faFacebookF, faLinkedin, faTwitter } from '@fortawesome/free-brands-svg-icons'
import { Column, Columns, Drawer } from '@/aerosol'
import { Text } from '@/components'
import styles from './ShareDrawer.styles.scss'

const socialIcons = {
  facebook: faFacebookF,
  twitter: faTwitter,
  linkedin: faLinkedin,
  sms: faCommentDots,
  email: faEnvelope,
  link: faLink,
}

interface Links {
  [platform: string]: string
}

interface Props extends Pick<DrawerProps, 'isOpen' | 'onClose'> {
  links: Links
}

const ShareDrawer: FC<Props> = ({ isOpen, onClose, links }) => {
  const renderShareLinks = () =>
    Object.keys(links).map((platform) => (
      <Column key={platform} columnWidth='small'>
        <a
          href={links[platform]}
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

export { ShareDrawer }
