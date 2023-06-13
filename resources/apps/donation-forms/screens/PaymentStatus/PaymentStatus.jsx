import { memo, useState } from 'react'
import { useRecoilValue } from 'recoil'
import { AnimatePresence, motion } from 'framer-motion'
import PropTypes from 'prop-types'
import Portal from '@/components/Portal/Portal'
import PaymentApproved from './components/PaymentApproved/PaymentApproved'
import PaymentDeclined from './components/PaymentDeclined/PaymentDeclined'
import ProcessingPayment from './components/ProcessingPayment/ProcessingPayment'
import paymentStatusState from '@/atoms/paymentStatus'
import styles from './PaymentStatus.scss'

const PaymentStatus = ({ navigateTo }) => {
  const [open, setOpen] = useState(true)

  const paymentStatus = useRecoilValue(paymentStatusState)

  const paymentWasApproved = paymentStatus === 'approved'
  const paymentWasDeclined = paymentStatus === 'declined'

  const usingPayPal = paymentStatus === 'using_paypal'
  const usingWalletPay = paymentStatus === 'using_wallet'
  const paymentIsProcessing = paymentStatus === 'processing' || usingPayPal || usingWalletPay

  const showPaymentStatus = (paymentWasApproved || paymentWasDeclined || paymentIsProcessing) && open

  const closePaymentStatus = () => setOpen(false)

  return (
    <Portal>
      <AnimatePresence>
        {showPaymentStatus && (
          <motion.div
            className={styles.root}
            initial={{ opacity: 0, scale: 0 }}
            animate={{ opacity: 1, scale: 1, transition: { delay: 0.2 } }}
            exit={{ x: '-100%' }}
            transition={{ duration: 0.3, ease: 'easeInOut' }}
          >
            {paymentWasApproved && <PaymentApproved closePaymentStatus={closePaymentStatus} />}

            <PaymentDeclined navigateTo={navigateTo} />
            <ProcessingPayment />
          </motion.div>
        )}
      </AnimatePresence>
    </Portal>
  )
}

PaymentStatus.propTypes = {
  navigateTo: PropTypes.func,
}

export default memo(PaymentStatus)
