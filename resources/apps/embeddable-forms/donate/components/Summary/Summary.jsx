import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import DonateButton from '@/components/DonateButton/DonateButton'
import CoverTheFees from '@/components/CoverTheFees/CoverTheFees'
import Captcha from '@/components/Captcha/Captcha'
import ErrorBox from '@/components/ErrorBox/ErrorBox'
import formatMoney from '@/utilities/formatMoney'
import styles from '@/components/Summary/Summary.scss'

const Summary = () => {
  const { donation, currency, variants, payment } = useContext(StoreContext)

  return (
    <div className={styles.root}>
      <div>You are about to give</div>

      <div className={styles.donationAmount}>
        {formatMoney(donation.totalWithFees, currency.chosen.code)}

        {variants.chosen.billing_period === 'onetime' ? '' : ` ${variants.chosen.billing_period}`}
      </div>

      <div className={styles.coverTheFeesContainer}>
        <CoverTheFees />
      </div>

      <Captcha />

      {payment.error.value && (
        <div className={styles.error}>
          <ErrorBox>{payment.error.value.message}</ErrorBox>
        </div>
      )}

      <DonateButton />
    </div>
  )
}

export default memo(Summary)
