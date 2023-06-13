import { AnimatePresence, motion } from 'framer-motion'
import PropTypes from 'prop-types'
import Portal from '@/components/Portal/Portal'
import Captcha from './components/Captcha/Captcha'
import useLocalization from '@/hooks/useLocalization'
import styles from './AreYouARobot.scss'

const AreYouARobot = ({ onVerify }) => {
  const t = useLocalization('screens.are_you_a_robot')

  return (
    <Portal>
      <AnimatePresence>
        <motion.div
          className={styles.root}
          initial={{ opacity: 0, scale: 0 }}
          animate={{ opacity: 1, scale: 1 }}
          exit={{ x: '-100%' }}
          transition={{ duration: 0.3, ease: 'easeInOut' }}
        >
          <h3>{t('heading')}</h3>
          <Captcha onVerify={onVerify} />
          <p>{t('description')}</p>
        </motion.div>
      </AnimatePresence>
    </Portal>
  )
}

AreYouARobot.propTypes = {
  onVerify: PropTypes.func.isRequired,
}

export default AreYouARobot
