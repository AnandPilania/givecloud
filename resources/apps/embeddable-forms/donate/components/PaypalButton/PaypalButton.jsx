import { memo, useEffect, useRef, useContext } from 'react'
import { StoreContext } from '@/root/store'
import styles from '@/components/PaypalButton/PaypalButton.scss'

const Givecloud = window.Givecloud

const PaypalButton = () => {
  const { submitDonation } = useContext(StoreContext)
  const buttonRef = useRef(null)

  useEffect(() => {
    const gateway = Givecloud.config.gateways.paypal

    let style = {
      type: 'checkout',
      size: 'medium',
      shape: 'pill',
      color: 'gold',
    }

    if (gateway === 'paypalcheckout') {
      style = {
        label: 'pay',
        size: 'responsive',
        shape: 'pill',
        color: 'gold',
        tagline: false,
      }
    }

    Givecloud.Gateway(gateway).renderButton({
      id: 'paypal-checkout',
      style,
      // validateForm: self.validator,
      onPayment: () => {
        buttonRef.current.click()
      },
    })
  }, [])

  return (
    <div className={styles.root}>
      <button ref={buttonRef} className={styles.hiddenButton} onClick={submitDonation} />
      <button id='paypal-checkout'></button>
    </div>
  )
}

export default memo(PaypalButton)
