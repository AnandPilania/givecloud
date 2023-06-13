import { memo, useEffect } from 'react'
import { useEffectOnce } from 'react-use'
import { useRecoilValue } from 'recoil'
import { useHistory } from 'react-router-dom'
import PropTypes from 'prop-types'
import { motion } from 'framer-motion'
import ReactConfetti from 'react-confetti'
import { Howl } from 'howler'
import Givecloud from 'givecloud'
import LogRocket from 'logrocket'
import AnimatedCheckmark from '@/components/AnimatedCheckmark/AnimatedCheckmark'
import useLocalization from '@/hooks/useLocalization'
import configState from '@/atoms/config'
import confettiOptionsState from '@/atoms/confettiOptions'
import contributionState from '@/atoms/contribution'
import { DOUBLE_THE_DONATION_SEARCH, MONTHLY_UPSELL, EMAIL_OPT_IN, THANK_YOU } from '@/constants/pathConstants'
import { assetUrl } from '@/utilities/assets'
import { setStateForCurrentVisit } from '@/utilities/config'
import styles from './PaymentApproved.scss'
import celebrationMp3 from './audio/celebration.mp3'

const PaymentApproved = ({ closePaymentStatus }) => {
  const t = useLocalization('screens.payment_approved')

  const config = useRecoilValue(configState)
  const confettiOptions = useRecoilValue(confettiOptionsState)
  const contribution = useRecoilValue(contributionState)
  const history = useHistory()

  useEffect(() => {
    if (config.enable_sound) {
      new Howl({ autoplay: true, src: [assetUrl(celebrationMp3)] })
    }
  }, [config])

  useEffectOnce(() => {
    setStateForCurrentVisit(null)

    if (contribution.account) {
      LogRocket.identify(`${Givecloud.config.site}:supporter_${contribution.account.id}`, {
        site: Givecloud.config.site,
        name: contribution.account.display_name,
        email: contribution.account.email,
        widget_type: config.widget_type,
      })
    }

    LogRocket.track('contribution_paid', {
      contribution_number: contribution.id,
      amount: contribution.line_items[0].price,
      dcc_amount: contribution.line_items[0].cover_costs_amount,
      dcc_type: contribution.cover_costs_type || 'none',
      revenue: contribution.total_price,
      currency: contribution.currency.code,
      payment_type: contribution.payment_type,
    })

    if (window.gtag) {
      window.gtag('event', 'purchase', {
        transaction_id: contribution.id,
        value: contribution.total_price,
        currency: contribution.currency.code,
        affiliation: 'Givecloud',
      })

      window.gtag('event', 'contribution_paid', {
        event_category: `fundraising_forms.${config.widget_type}`,
        event_label: contribution.id,
        event_value: parseInt(contribution.total_amount, 10),
      })
    }

    if (window.fbq) {
      window.fbq('track', 'Purchase', {
        currency: contribution.currency.code,
        value: contribution.total_price,
      })
    }

    setTimeout(() => {
      closePaymentStatus()

      const variant = config.variants.find((variant) => variant.id === contribution.line_items[0].variant_id)
      const canPerformUpgrade = variant.billing_period === 'onetime' && contribution.payment_method_saved
      const monthlyUpsellPath = config.upsell.enabled && canPerformUpgrade && MONTHLY_UPSELL
      const doubleTheDonationPath = config.double_the_donation.enabled && DOUBLE_THE_DONATION_SEARCH
      const emailOptInPath = config.email_optin_enabled && EMAIL_OPT_IN

      history.push(monthlyUpsellPath || doubleTheDonationPath || emailOptInPath || THANK_YOU)
    }, 5000)
  })

  return (
    <div className={styles.root}>
      <div className={styles.confetti}>
        <ReactConfetti {...confettiOptions} />
      </div>

      <AnimatedCheckmark />

      <motion.div initial={{ opacity: 0, y: '100%' }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.8 }}>
        <h3>{t('heading')}</h3>
        <p>{t('description', { email: contribution.billing_address.email })}</p>
      </motion.div>
    </div>
  )
}

PaymentApproved.propTypes = {
  closePaymentStatus: PropTypes.func,
}

export default memo(PaymentApproved)
