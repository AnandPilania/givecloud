import { memo } from 'react'
import { useRecoilState } from 'recoil'
import { AnimatePresence, motion } from 'framer-motion'
import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faTriangleExclamation } from '@fortawesome/pro-regular-svg-icons'
import { faArrowLeft } from '@fortawesome/pro-solid-svg-icons'
import Button from '@/components/Button/Button'
import useLocalization from '@/hooks/useLocalization'
import paymentStatusState from '@/atoms/paymentStatus'
import paymentFailureState from '@/atoms/paymentFailure'
import styles from './PaymentDeclined.scss'

const PaymentDeclined = ({ navigateTo }) => {
  const t = useLocalization('screens.payment_declined')
  const [paymentFailure, setPaymentFailure] = useRecoilState(paymentFailureState)
  const [paymentStatus, setPaymentStatus] = useRecoilState(paymentStatusState)

  const paymentWasDeclined = paymentStatus === 'declined'

  const handleTryAgainClick = () => {
    if (navigateTo) {
      navigateTo('pay_with_credit_card', 'POP', 'set')
    }

    setPaymentStatus(null)
    setPaymentFailure(null)
  }

  return (
    <AnimatePresence>
      {paymentWasDeclined && (
        <div className={styles.root}>
          <motion.div
            className={styles.content}
            initial={{ opacity: 0, y: '100%' }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, transition: { duration: 0.3 } }}
            transition={{ duration: 0.8 }}
          >
            <div className={styles.warningIcon}>
              <FontAwesomeIcon icon={faTriangleExclamation} />
            </div>

            <h3>{t('heading')}</h3>
            <p>{paymentFailure.friendly_message}</p>
            <p>{paymentFailure.corrective_action}</p>

            <Button className={styles.tryAgainBtn} onClick={handleTryAgainClick}>
              <FontAwesomeIcon icon={faArrowLeft} /> {t('try_again')}
            </Button>

            <p className={styles.errorMessage}>{t('error_from_bank', { error: paymentFailure.error })}</p>
          </motion.div>
        </div>
      )}
    </AnimatePresence>
  )
}

PaymentDeclined.propTypes = {
  navigateTo: PropTypes.func,
}

export default memo(PaymentDeclined)
