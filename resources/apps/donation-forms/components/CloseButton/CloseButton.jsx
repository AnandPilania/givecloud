import { memo } from 'react'
import { faTimes } from '@fortawesome/pro-regular-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import styles from './CloseButton.scss'

const CloseButton = ({ onClick, className, ...restProps }) => {
  return (
    <button onClick={onClick} className={classnames(styles.root, className)} aria-label='Close donation' {...restProps}>
      <FontAwesomeIcon icon={faTimes} fixedWidth />
    </button>
  )
}

CloseButton.propTypes = {
  onClick: PropTypes.func.isRequired,
  className: PropTypes.string,
}

export default memo(CloseButton)
