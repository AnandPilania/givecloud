import PropTypes from 'prop-types'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faExternalLink } from '@fortawesome/pro-regular-svg-icons'
import styles from './ExternalLinkIcon.scss'

const ExternalLinkIcon = ({ className = '' }) => (
  <FontAwesomeIcon
    title={faExternalLink.iconName}
    aria-label='external-link'
    className={classnames(styles.root, className)}
    icon={faExternalLink}
  />
)

ExternalLinkIcon.propTypes = {
  className: PropTypes.string,
}

export { ExternalLinkIcon }
