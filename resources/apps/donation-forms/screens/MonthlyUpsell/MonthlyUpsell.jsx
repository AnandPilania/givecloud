import { useState } from 'react'
import { useRecoilValue } from 'recoil'
import { Link } from 'react-router-dom'
import { round } from 'lodash'
import { faArrowUp } from '@fortawesome/pro-light-svg-icons'
import HerospaceIcon from '@/components/HerospaceIcon/HerospaceIcon'
import Screen from '@/components/Screen/Screen'
import AmountButton from './components/AmountButton'
import useAnalytics from '@/hooks/useAnalytics'
import useCurrencyFormatter from '@/hooks/useCurrencyFormatter'
import useLocalization from '@/hooks/useLocalization'
import useSubstitution from '@/hooks/useSubstitution'
import configState from '@/atoms/config'
import contributionState from '@/atoms/contribution'
import { DOUBLE_THE_DONATION_SEARCH, EMAIL_OPT_IN, THANK_YOU } from '@/constants/pathConstants'
import styles from './MonthlyUpsell.scss'
import moment from 'moment'

const MonthlyUpsell = () => {
  const t = useLocalization('screens.monthly_upsell')
  const substitute = useSubstitution('screens.monthly_upsell')

  const config = useRecoilValue(configState)
  const collectEvent = useAnalytics({ collectOnce: true })
  const contribution = useRecoilValue(contributionState)
  const formatCurrency = useCurrencyFormatter({ currencyCode: contribution.currency.code })
  const [upgrading, setUpgrading] = useState(false)

  const customAmount = contribution.subtotal_price > 299 ? round(contribution.subtotal_price / 12) : null
  const notCustomAmount = !customAmount

  const description = substitute('description_html', config.upsell.description, {
    from: formatCurrency(contribution.total_price),
    into: formatCurrency(Math.round(contribution.total_price * 12)),
  })

  const noThanksLink = () => {
    const doubleTheDonationPath = config.double_the_donation.enabled && DOUBLE_THE_DONATION_SEARCH
    const emailOptInPath = config.email_optin_enabled && EMAIL_OPT_IN

    return doubleTheDonationPath || emailOptInPath || THANK_YOU
  }

  collectEvent({ event_name: 'offered_upsell' })

  const AmountButtons = (
    <>
      <AmountButton customAmount={19} upgrading={upgrading} setUpgrading={setUpgrading} />
      <AmountButton
        customAmount={9}
        upgrading={upgrading}
        setUpgrading={setUpgrading}
        fineprint={t('every_little_bit')}
      />
    </>
  )

  return (
    <Screen className={styles.root} showBackButton={false}>
      <HerospaceIcon icon={faArrowUp} />
      <h3>{config.upsell.heading || t('heading')}</h3>
      <p dangerouslySetInnerHTML={description} />
      <p>{t('starts_on', { date: moment().add(1, 'months').format('MMM D') })}</p>

      {customAmount && (
        <>
          <div className={styles.options}>
            <AmountButton customAmount={customAmount} upgrading={upgrading} setUpgrading={setUpgrading} />
          </div>
          <div className={styles.options}>{AmountButtons}</div>
        </>
      )}

      {notCustomAmount && AmountButtons}

      <Link className={styles.noThanks} to={noThanksLink()}>
        {t('no_thanks')}
      </Link>
    </Screen>
  )
}

export default MonthlyUpsell
