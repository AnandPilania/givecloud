import { memo } from 'react'
import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowToBottom, faCommentDots, faEnvelope } from '@fortawesome/pro-regular-svg-icons'
import { faFacebookF, faLinkedin, faTwitter } from '@fortawesome/free-brands-svg-icons'

const socialIcons = {
  facebook: faFacebookF,
  twitter: faTwitter,
  linkedin: faLinkedin,
  sms: faCommentDots,
  email: faEnvelope,
  download: faArrowToBottom,
}

const SocialLink = ({ platform, href }) => {
  const attributes = platform === 'download' ? { download: true } : { target: '_blank', rel: 'noreferrer' }

  return (
    <li>
      <a href={href} {...attributes} rel='noreferrer' target='_blank'>
        <FontAwesomeIcon icon={socialIcons[platform]} />
      </a>
    </li>
  )
}

SocialLink.propTypes = {
  platform: PropTypes.string,
  href: PropTypes.string,
}

export default memo(SocialLink)
