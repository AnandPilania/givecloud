import classnames from 'classnames'
import PropTypes from 'prop-types'
import styles from './OverflowFadeoutBox.scss'

const OverflowFadeoutBox = ({ children, className }) => {
  return <div className={classnames(styles.root, className)}>{children}</div>
}

OverflowFadeoutBox.propTypes = {
  children: PropTypes.node,
  className: PropTypes.string,
}

export default OverflowFadeoutBox
