import { memo } from 'react'
import { useRecoilState, useRecoilValue } from 'recoil'
import { useHistory } from 'react-router-dom'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { AnimatePresence, motion } from 'framer-motion'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faUniversity } from '@fortawesome/pro-regular-svg-icons'
import { faCreditCard } from '@fortawesome/pro-solid-svg-icons'
import Givecloud from 'givecloud'
import Button from '@/components/Button/Button'
import Drawer from '@/components/Drawer/Drawer'
import CoverCostsSelector from '@/components/CoverCostsSelector/CoverCostsSelector'
import PaymentStatus from '@/screens/PaymentStatus/PaymentStatus'
import PayPalButton from './components/PayPalButton/PayPalButton'
import WalletPayButton from './components/WalletPayButton/WalletPayButton'
import useAnalytics from '@/hooks/useAnalytics'
import useCurrencyFormatter from '@/hooks/useCurrencyFormatter'
import useLocalization from '@/hooks/useLocalization'
import formInputState from '@/atoms/formInput'
import pendingContributionState from '@/atoms/pendingContribution'
import { noop } from '@/utilities/helpers'
import MastercardIcon from './images/MastercardIcon.svg?react'
import AMEXIcon from './images/AMEXIcon.svg?react'
import VisaIcon from './images/VisaIcon.svg?react'
import { CHECKOUT } from '@/constants/pathConstants'
import styles from './PaymentMethodSelector.scss'

const PaymentMethodSelector = ({ open, onClose = noop }) => {
  const t = useLocalization('screens.choose_payment_method.payment_method_selector')

  const history = useHistory()
  const collectEvent = useAnalytics({ collectOnce: true })

  const [formInput, setFormInput] = useRecoilState(formInputState)
  const pendingContribution = useRecoilValue(pendingContributionState)
  const formatCurrency = useCurrencyFormatter({ showCurrencyCode: true })

  const creditCardEnabled = Boolean(Givecloud.config.gateways.credit_card)

  const bankAccountGateway = Givecloud.PaymentTypeGateway('bank_account')
  const bankAccountEnabled = Boolean(bankAccountGateway?.canMakeAchPayment(pendingContribution.currency_code))

  const onClickPayWithCreditCard = (paymentType) => {
    return () => {
      collectEvent({ event_name: 'traditional_checkout_click' })

      setFormInput({ ...formInput, payment_type: paymentType })
      history.push(CHECKOUT)
      onClose()
    }
  }

  if (open) {
    collectEvent({ event_name: 'payment_method_drawer_opened' })
  }

  return (
    <Drawer className={styles.root} open={open} onClose={onClose}>
      <div className={styles.summary}>
        <div className={styles.summaryContent}>
          {t('donate')}
          <strong>
            {t(pendingContribution.is_monthly ? 'monthly_amount' : 'amount', {
              amount: formatCurrency(pendingContribution.total),
            })}
          </strong>
          <CoverCostsSelector className={styles.coverCostsContainer} />
        </div>
        <AnimatePresence>
          {open && (
            <div className={styles.sheen}>
              <motion.div initial={{ x: '-100%' }} animate={{ x: '100%', transition: { duration: 1 } }}></motion.div>
            </div>
          )}
        </AnimatePresence>
      </div>

      <WalletPayButton closeDrawer={onClose} />
      <PayPalButton closeDrawer={onClose} />

      <div className={styles.altPaymentMethods}>
        {bankAccountEnabled && (
          <Button
            className={classnames(styles.altPaymentMethodButton, styles.bankAccountBtn)}
            onClick={onClickPayWithCreditCard('bank_account')}
          >
            <span>
              <FontAwesomeIcon icon={faUniversity} /> {t('bank')}
            </span>
          </Button>
        )}

        {creditCardEnabled && (
          <Button className={styles.altPaymentMethodButton} onClick={onClickPayWithCreditCard('credit_card')}>
            {bankAccountEnabled ? (
              <span>
                <FontAwesomeIcon icon={faCreditCard} /> {t('credit')}
              </span>
            ) : (
              <span>
                <MastercardIcon />
                <AMEXIcon />
                <VisaIcon />
              </span>
            )}
          </Button>
        )}
      </div>

      <PaymentStatus />
    </Drawer>
  )
}

PaymentMethodSelector.propTypes = {
  open: PropTypes.bool.isRequired,
  onClose: PropTypes.func,
}

export default memo(PaymentMethodSelector)
