import { memo } from 'react'
import { AnimatePresence, motion } from 'framer-motion'
import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheck } from '@fortawesome/free-solid-svg-icons'
import Button from '@/components/Button/Button'
import Portal from '@/components/Portal/Portal'
import useLocalization from '@/hooks/useLocalization'
import styles from './DccInformationDialog.scss'

const DccInformationDialog = ({ showDialog, dismissDialog }) => {
  const t = useLocalization('components.cover_costs_selector.information')

  const backdropVariants = {
    hide: { opacity: 0 },
    show: { opacity: 1 },
  }

  const dialogVariants = {
    hide: { transition: { type: 'tween' }, y: '-100vh' },
    show: { transition: { type: 'tween' }, y: 0 },
  }

  return (
    <AnimatePresence>
      {showDialog && (
        <Portal>
          <motion.div className={styles.root} variants={backdropVariants} initial='hide' animate='show' exit='hide'>
            <motion.div
              role='dialog'
              aria-modal={true}
              aria-labelledby='achBankAccountDialogLabel'
              className={styles.dialog}
              variants={dialogVariants}
            >
              {/* prettier-ignore */}
              <div className={styles.content}>
                <h1>{t('help_cover_costs_fees')}</h1>
                <p>{t('one_of_the_most_cost_effective_ways')}</p>
                <p>{t('help_make_the_most_of_your_donation')}</p>
                <ul>
                  <li><FontAwesomeIcon className={styles.icon} icon={faCheck} /> {t('processing_bank_fees')}</li>
                  <li><FontAwesomeIcon className={styles.icon} icon={faCheck} /> {t('it_security_costs')}</li>
                  <li><FontAwesomeIcon className={styles.icon} icon={faCheck} /> {t('software_costs')}</li>
                </ul>
              </div>

              <div className={styles.controls}>
                <Button onClick={dismissDialog}>Close</Button>
              </div>
            </motion.div>
          </motion.div>
        </Portal>
      )}
    </AnimatePresence>
  )
}

DccInformationDialog.propTypes = {
  showDialog: PropTypes.bool.isRequired,
  dismissDialog: PropTypes.func.isRequired,
}

export default memo(DccInformationDialog)
