import { memo, useContext } from 'react'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import { supportedPrimaryColors } from '@/constants/styleConstants'
import PaypalButton from '@/components/PaypalButton/PaypalButton'
import styles from '@/components/DonateButton/DonateButton.scss'

const DonateButton = () => {
  const { payment, submitDonation, primaryColor, theme } = useContext(StoreContext)
  const isLightTheme = theme === 'light'

  const {
    bgColor,
    hoverBgColorLight,
    focusBorderColorDark,
    focusRingColorLight,
    focusRingColorDark,
    activeBgColorDark,
  } = supportedPrimaryColors[primaryColor] || {}

  return (
    <div className={styles.root}>
      {payment.method.chosen === 'paypal' ? (
        <PaypalButton submitDonation={submitDonation} />
      ) : (
        <button
          onClick={submitDonation}
          className={classnames(
            styles.donateNowButton,
            bgColor,
            hoverBgColorLight,
            focusBorderColorDark,
            activeBgColorDark,
            focusRingColorLight,
            !isLightTheme && focusRingColorDark
          )}
        >
          Donate Now
        </button>
      )}
    </div>
  )
}

export default memo(DonateButton)
