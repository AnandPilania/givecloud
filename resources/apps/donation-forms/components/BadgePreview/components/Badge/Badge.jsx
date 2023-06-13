import PropTypes from 'prop-types'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faBolt, faFire, faHeart, faSparkles } from '@fortawesome/pro-solid-svg-icons'
import { motion } from 'framer-motion'
import useLocalization from '@/hooks/useLocalization'
import styles from './Badge.scss'

const Badge = ({ className, percentage }) => {
  const t = useLocalization('components.badge_preview')

  const badgeIcons = {
    0.1: faFire,
    1: faBolt,
    2: faBolt,
    5: faSparkles,
    10: faSparkles,
    15: faHeart,
    40: faHeart,
  }

  return (
    <motion.div
      className={classnames(styles.root, className)}
      initial={{ rotateX: 180 }}
      animate={{ rotateX: 0 }}
      exit={{ rotateX: 180 }}
      transition={{ duration: 0.4, ease: 'easeInOut' }}
    >
      <FontAwesomeIcon icon={badgeIcons[percentage]} />
      <span dangerouslySetInnerHTML={t('top_x_of_donors_html', { percentage })}></span>
      <div className={styles.sheen}></div>
    </motion.div>
  )
}

Badge.propTypes = {
  className: PropTypes.string,
  percentage: PropTypes.number.isRequired,
}

export default Badge
