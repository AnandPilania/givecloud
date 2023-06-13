import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { ICONS } from './icons'

const Icon = ({ className = '', icon = '', isFixedWidth = false, spin = false, size = '1x' }) => {
  const faIcon = ICONS[icon]

  return (
    <FontAwesomeIcon
      className={className}
      icon={faIcon}
      fixedWidth={isFixedWidth}
      spin={spin}
      aria-label={icon}
      size={size}
    />
  )
}

Icon.propTypes = {
  className: PropTypes.string,
  icon: PropTypes.string.isRequired,
  isFixedWidth: PropTypes.bool,
  spin: PropTypes.bool,
  size: PropTypes.string,
}

export { Icon }
