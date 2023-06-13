import { memo, useCallback, useEffect } from 'react'
import { useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import Givecloud from 'givecloud'
import Button from '@/components/Button/Button'
import useCheckout from '@/hooks/useCheckout'
import useLocalization from '@/hooks/useLocalization'
import useToastErrors from '@/hooks/useToastErrors'
import formInputState from '@/atoms/formInput'
import PayPalIcon from './images/PayPalIcon.svg?react'
import PayPalMarkIcon from './images/PayPalMarkIcon.svg?react'
import styles from './PayPalButton.scss'

const PayPalButton = ({ className, closeDrawer }) => {
  const t = useLocalization('screens.choose_payment_method.payment_method_selector')

  const gateway = Givecloud.PaymentTypeGateway('paypal')

  const formInput = useRecoilValue(formInputState)
  const isMonthly = formInput.item.recurring_frequency === 'monthly'
  const payPalSupported = isMonthly ? !!gateway?.referenceTransactions : !!gateway

  const attemptCheckout = useCheckout()
  const toastError = useToastErrors()

  const setupPayPalButton = useCallback(() => {
    const handlePayPal = async () => {
      try {
        await attemptCheckout({ payment_type: 'paypal' })
      } catch (err) {
        closeDrawer()

        if (err === 'PAYPAL_UNKNOWN_ERROR') {
          toastError(t('paypal_unknown_error'))
        } else if (err !== 'PAYPAL_REQUEST_CANCELLED') {
          toastError(err)
        }
      }
    }

    gateway.setupButton('inputPayPalButton', handlePayPal)
  }, [attemptCheckout, closeDrawer, gateway, t, toastError])

  useEffect(() => {
    // wrapping in setTimeout to ensure time for actual DOM to have been updated
    setTimeout(() => payPalSupported && setupPayPalButton(), 10)
  }, [payPalSupported, setupPayPalButton])

  if (!payPalSupported) {
    return null
  }

  return (
    <div className={classnames(styles.root, className)}>
      <Button id='inputPayPalButton' className={styles.payPalButton}>
        <PayPalMarkIcon />
        <PayPalIcon />
      </Button>
    </div>
  )
}

PayPalButton.propTypes = {
  className: PropTypes.string,
  closeDrawer: PropTypes.func,
}

export default memo(PayPalButton)
