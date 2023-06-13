import classnames from 'classnames'
import PropTypes from 'prop-types'
import { AnimatePresence, motion } from 'framer-motion'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { isPrimaryColourDark } from '@/utilities/theme'
import styles from './HerospaceIcon.scss'

const HerospaceIcon = ({ className, icon, controls }) => {
  const backgroundVariants = {
    hide: {
      opacity: 0,
      scale: 0,
    },
    show: {
      opacity: 1,
      scale: 1,
    },
  }

  const iconVariants = {
    hide: {
      opacity: 0,
      y: '-150%',
    },
    show: {
      opacity: 1,
      y: '0%',
      transition: { duration: 0.3, delay: 0.5 },
    },
  }

  return (
    <AnimatePresence>
      <motion.div
        variants={backgroundVariants}
        initial='hide'
        animate={controls || 'show'}
        transition={{ duration: 0.4, delay: 0.2 }}
        className={classnames(styles.root, className, isPrimaryColourDark && styles.darkPrimaryColour)}
      >
        <motion.div variants={iconVariants} initial='hide' animate={controls || 'show'}>
          <FontAwesomeIcon icon={icon} />
        </motion.div>
      </motion.div>
    </AnimatePresence>
  )
}

HerospaceIcon.propTypes = {
  className: PropTypes.string,
  icon: PropTypes.object.isRequired,
  controls: PropTypes.any,
}

export default HerospaceIcon
