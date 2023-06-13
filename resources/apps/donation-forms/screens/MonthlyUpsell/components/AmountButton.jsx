import { useRef, useState } from 'react'
import { useHistory } from 'react-router-dom'
import { useRecoilState, useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import { round, uniqueId } from 'lodash'
import { delay } from 'nanodelay'
import ReactConfetti from 'react-confetti'
import Givecloud from 'givecloud'
import useLocalization from '@/hooks/useLocalization'
import useCurrencyFormatter from '@/hooks/useCurrencyFormatter'
import Button from '@/components/Button/Button'
import FloatingIcons from '@/components/FloatingIcons/FloatingIcons'
import configState from '@/atoms/config'
import contributionState from '@/atoms/contribution'
import confettiOptionsState from '@/atoms/confettiOptions'
import { DOUBLE_THE_DONATION_SEARCH, EMAIL_OPT_IN, THANK_YOU } from '@/constants/pathConstants'
import styles from './AmountButton.scss'

const minimumAmount = 9

const AmountButton = ({ customAmount, shrinkBy = 1, upgrading, setUpgrading, fineprint }) => {
  const t = useLocalization('screens.monthly_upsell')

  const config = useRecoilValue(configState)
  const confettiOptions = useRecoilValue(confettiOptionsState)
  const [contribution, setContribution] = useRecoilState(contributionState)
  const formatCurrency = useCurrencyFormatter({ currencyCode: contribution.currency.code })
  const history = useHistory()

  const buttonRef = useRef()
  const buttonLabel = customAmount || shrinkBy !== 1 ? 'per_month_html' : 'make_per_month_html'

  const [floatingIcons, setFloatingIcons] = useState(null)

  const amount = customAmount || round((contribution.total_price - contribution.cover_costs_amount) / shrinkBy, 2)
  const total = customAmount || round(contribution.total_price / shrinkBy, 2)
  const coverCostsAmount = total - amount

  const upgradeContribution = async () => {
    const variant = config.variants.find((variant) => variant.billing_period === 'monthly')

    const { cart } = await Givecloud.Cart(contribution.id).upgradeItem(contribution.line_items[0].id, {
      variant_id: variant?.id || contribution.line_items[0].variant_id,
      amt: amount,
      dcc: coverCostsAmount,
      recurring_frequency: 'monthly',
    })

    return cart
  }

  const handleOnClick = async (e) => {
    const buttonRect = buttonRef.current.getBoundingClientRect()

    setFloatingIcons({
      key: uniqueId('AmountButton'),
      offset: {
        top: `${e.clientY - buttonRect.top + window.scrollY}px`,
        left: `${e.clientX - buttonRect.left + window.scrollX}px`,
      },
    })

    setUpgrading(true)
    setContribution(await upgradeContribution())

    // delay for short time to allow the floating icons
    // and confetti to do their voodoo
    await delay(3000)

    const doubleTheDonationPath = config.double_the_donation.enabled && DOUBLE_THE_DONATION_SEARCH
    const emailOptInPath = config.email_optin_enabled && EMAIL_OPT_IN

    history.push(doubleTheDonationPath || emailOptInPath || THANK_YOU)
  }

  if (shrinkBy > 1 && total < minimumAmount) {
    return null
  }

  return (
    <div className={styles.root}>
      {floatingIcons && (
        <div key={floatingIcons.key} className={styles.confetti}>
          <ReactConfetti {...confettiOptions} />
        </div>
      )}

      <Button ref={buttonRef} className={styles.amountButton} onClick={handleOnClick} disabled={upgrading}>
        <span dangerouslySetInnerHTML={t(buttonLabel, { amount: formatCurrency(total) })}></span>
        {floatingIcons && (
          <FloatingIcons iconKey={floatingIcons.key} offset={floatingIcons.offset} condition={true} large />
        )}
      </Button>

      {fineprint && <div className={styles.fineprint}>{fineprint}</div>}
    </div>
  )
}

AmountButton.propTypes = {
  customAmount: PropTypes.number,
  shrinkBy: PropTypes.number,
  upgrading: PropTypes.bool.isRequired,
  setUpgrading: PropTypes.func.isRequired,
  fineprint: PropTypes.string,
}

export default AmountButton
