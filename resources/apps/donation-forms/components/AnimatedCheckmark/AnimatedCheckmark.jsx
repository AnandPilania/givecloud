import { memo } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheck } from '@fortawesome/pro-light-svg-icons'
import styles from './AnimatedCheckmark.scss'

const AnimatedCheckmark = () => {
  return (
    <div className={styles.checkIcon}>
      <div className={styles.greenCircle}>
        <FontAwesomeIcon icon={faCheck} />
      </div>
      <div className={styles.whiteCircle}></div>
    </div>
  )
}

export default memo(AnimatedCheckmark)
