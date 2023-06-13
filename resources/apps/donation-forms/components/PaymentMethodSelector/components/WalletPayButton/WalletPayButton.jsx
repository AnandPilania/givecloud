import { memo, useCallback, useEffect, useRef } from 'react'
import { useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faApple } from '@fortawesome/free-brands-svg-icons'
import Givecloud from 'givecloud'
import useCheckout from '@/hooks/useCheckout'
import useLocalization from '@/hooks/useLocalization'
import useToastErrors from '@/hooks/useToastErrors'
import useWalletPay from '@/hooks/useWalletPay'
import localeState from '@/atoms/locale'
import GPayIcon from './images/GPayIcon.svg?react'
import styles from './WalletPayButton.scss'

const WalletPayButton = ({ className, closeDrawer }) => {
  const t = useLocalization('screens.choose_payment_method.payment_method_selector')

  const locale = useRecoilValue(localeState)
  const buttonLang = locale.substring(0, 2)

  const googlePayButtonRef = useRef()

  const attemptCheckout = useCheckout()
  const canMakePayment = useWalletPay()
  const toastError = useToastErrors()

  const attemptWalletPay = useCallback(
    async (walletType) => {
      try {
        closeDrawer()
        await attemptCheckout({ payment_type: 'wallet_pay', wallet_type: walletType })
      } catch (err) {
        if (err !== 'PAYMENT_REQUEST_CANCELLED') {
          toastError(err)
        }
      }
    },
    [attemptCheckout, closeDrawer, toastError]
  )

  const gateway = Givecloud.PaymentTypeGateway('wallet_pay')

  const useApplePayButton = !!canMakePayment?.applePay
  const useGooglePayButton = !!(canMakePayment?.googlePay && gateway.createGooglePayButton)
  const useUnofficialGooglePayButton = !!(canMakePayment?.googlePay && !gateway.createGooglePayButton)

  useEffect(() => {
    if (useGooglePayButton) {
      gateway.createGooglePayButton(googlePayButtonRef.current, {
        buttonColor: 'black',
        buttonType: 'donate',
        buttonLocale: buttonLang,
        buttonSizeMode: 'fill',
        onClick: (e) => {
          e.preventDefault()
          attemptWalletPay('googlePay')
        },
      })
    }
  }, [attemptWalletPay, buttonLang, gateway, googlePayButtonRef, useGooglePayButton])

  if (!useApplePayButton && !useGooglePayButton && !useUnofficialGooglePayButton) {
    return null
  }

  return (
    <div className={classnames(styles.root, className)}>
      {useApplePayButton && (
        <button className={styles.applePayButton} onClick={() => attemptWalletPay('applePay')}>
          <div className={styles.applePayOfficialButton} lang={buttonLang}></div>
          <div className={styles.applePayUnofficialButton}>
            <FontAwesomeIcon icon={faApple} size='lg' /> &nbsp; Pay
          </div>
        </button>
      )}

      {useGooglePayButton && (
        <div className={classnames(styles.googlePayButton, styles.googlePayUnofficialButton)}>
          <div ref={googlePayButtonRef}></div>
        </div>
      )}

      {useUnofficialGooglePayButton && (
        <button className={styles.googlePayButton} onClick={() => attemptWalletPay('googlePay')}>
          <span>{t('donate_with')}</span> &nbsp; <GPayIcon />
        </button>
      )}
    </div>
  )
}

WalletPayButton.propTypes = {
  className: PropTypes.string,
  closeDrawer: PropTypes.func,
}

export default memo(WalletPayButton)
