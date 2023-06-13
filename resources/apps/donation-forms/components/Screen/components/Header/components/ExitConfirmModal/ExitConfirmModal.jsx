import { memo, useState } from 'react'
import { useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import { Dialog } from '@headlessui/react'
import { motion, AnimatePresence } from 'framer-motion'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/pro-light-svg-icons'
import useLocalization from '@/hooks/useLocalization'
import configState from '@/atoms/config'
import styles from './ExitConfirmModal.scss'

const ExitConfirmModal = ({ isOpen = false, dismiss, close }) => {
  const t = useLocalization('components.screen.exit_confirm_modal')

  const config = useRecoilValue(configState)
  const exitDescription = config.exit_confirmation.description || t('are_you_sure')

  const exitBtnLabel = window.parentIFrame ? 'close' : 'exit'
  const [cancelButtonRef, setCancelButtonRef] = useState(null)

  return (
    <AnimatePresence>
      {isOpen && (
        <Dialog as='div' static className={styles.root} initialFocus={cancelButtonRef} open={isOpen} onClose={dismiss}>
          <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}>
            <Dialog.Overlay className={styles.overlay} />
          </motion.div>

          <motion.div
            className={styles.content}
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0, scale: 0.95 }}
          >
            <Dialog.Title as='h3' className={styles.title}>
              {exitDescription}
            </Dialog.Title>

            {/*<p className={styles.text}>{t('message')}</p>*/}

            <div className={styles.actions}>
              <button ref={setCancelButtonRef} type='button' className={styles.cancelButton} onClick={dismiss}>
                {t('cancel')}
              </button>

              {/*<button type='button' className={styles.remindButton}>
                Remind me in July
              </button>*/}

              <button type='button' className={styles.exitButton} onClick={close}>
                <span>
                  {t(exitBtnLabel)} <FontAwesomeIcon icon={faArrowRight} />
                </span>
              </button>
            </div>
          </motion.div>
        </Dialog>
      )}
    </AnimatePresence>
  )
}

ExitConfirmModal.propTypes = {
  isOpen: PropTypes.bool.isRequired,
  dismiss: PropTypes.func.isRequired,
  close: PropTypes.func.isRequired,
}

export default memo(ExitConfirmModal)
