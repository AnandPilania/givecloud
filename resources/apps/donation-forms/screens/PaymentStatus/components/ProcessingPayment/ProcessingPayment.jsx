import { memo } from 'react'
import { useRecoilValue } from 'recoil'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faLockAlt } from '@fortawesome/pro-regular-svg-icons'
import classnames from 'classnames'
import { isPrimaryColourDark } from '@/utilities/theme'
import Messages from './components/Messages'
import paymentStatusState from '@/atoms/paymentStatus'
import Spinner from './images/Spinner.svg?react'
import styles from './ProcessingPayment.scss'

const ProcessingPayment = () => {
  const paymentStatus = useRecoilValue(paymentStatusState)

  const usingPayPal = paymentStatus === 'using_paypal'
  const usingWalletPay = paymentStatus === 'using_wallet'
  const isProcessingPayment = paymentStatus === 'processing' || usingPayPal || usingWalletPay
  const showMessages = !usingPayPal && !usingWalletPay

  if (!isProcessingPayment) {
    return null
  }

  return (
    <div className={classnames(styles.root, isPrimaryColourDark && styles.darkPrimaryColour)}>
      <div className={styles.spinner}>
        <Spinner />

        <div className={styles.icon}>
          <FontAwesomeIcon icon={faLockAlt} />
        </div>
      </div>

      {showMessages && <Messages className={styles.message} />}
    </div>
  )
}

export default memo(ProcessingPayment)
