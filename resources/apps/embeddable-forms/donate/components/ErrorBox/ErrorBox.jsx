import { memo } from 'react'
import PropTypes from 'prop-types'
import styles from '@/components/ErrorBox/ErrorBox.scss'

const ErrorBox = ({ children, ...props }) => (
  <div className={styles.root} {...props}>
    {children}
  </div>
)

ErrorBox.propTypes = {
  children: PropTypes.node,
}

export default memo(ErrorBox)
