import { memo } from 'react'
import PropTypes from 'prop-types'
import styles from '@/components/MuxPreStreamMessage/MuxPreStreamMessage.scss'
import classNames from 'classnames'

const MuxPreStreamMessage = ({ prestreamMessageLine1, prestreamMessageLine2, themeStyle }) => (
  <div className={classNames(styles.root, themeStyle === 'dark' ? styles.dark : styles.light)}>
    <h2>{prestreamMessageLine1 || `The Stream Hasn't Started Yet`}</h2>

    {!!prestreamMessageLine2 && <h3>{prestreamMessageLine2}</h3>}
  </div>
)

MuxPreStreamMessage.propTypes = {
  prestreamMessageLine1: PropTypes.string,
  prestreamMessageLine2: PropTypes.string,
  themeStyle: PropTypes.string,
}

export default memo(MuxPreStreamMessage)
