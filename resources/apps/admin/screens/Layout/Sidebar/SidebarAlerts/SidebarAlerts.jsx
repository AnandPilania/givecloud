import { useRecoilValue } from 'recoil'
import configState from '@/atoms/config'
import { Chip } from '@/aerosol'
import { PaymentMethodProblemChip } from '@/screens/Layout/Sidebar/SidebarAlerts/PaymentMethodProblemChip'
import { WARNING } from '@/shared/constants/theme'
import { SETTINGS_BILLING_PATH, SETTINGS_PAYMENT_PATH } from '@/constants/pathConstants'
import styles from './SidebarAlerts.scss'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faClock, faToggleOn } from '@fortawesome/pro-regular-svg-icons'
import { BillingWarningChip } from '@/screens/Layout/Sidebar/SidebarAlerts/BillingWarningChip'

const SidebarAlerts = () => {
  const {
    canUserViewBilling = false,
    hasOutstandingInvoice = false,
    isDevelopment = false,
    isGivecloudExpress = false,
    isMissingPaymentMethod = false,
    isTestMode = false,
    isTrial = false,
    trialDaysRemaining = 0,
  } = useRecoilValue(configState)

  if (isGivecloudExpress) return null

  const showTrialDaysRemainingChip = isTrial && !!trialDaysRemaining
  const showPaymentMethodProblemChip = !isTrial && isMissingPaymentMethod && !isDevelopment

  if (!showTrialDaysRemainingChip && !isTestMode && !showPaymentMethodProblemChip) return null

  const chipProps = {}
  if (canUserViewBilling) {
    chipProps.href = SETTINGS_BILLING_PATH
  }

  return (
    <div className={styles.root}>
      {showTrialDaysRemainingChip && (
        <Chip invertTheme {...chipProps}>
          <FontAwesomeIcon icon={faClock} />
          <span className='ml-2'>
            Free Trial Ends <strong>in {trialDaysRemaining} Days</strong>
          </span>
        </Chip>
      )}

      {isTestMode && (
        <Chip theme={WARNING} href={SETTINGS_PAYMENT_PATH}>
          <FontAwesomeIcon icon={faToggleOn} />
          <span className='ml-2'>
            You Are <strong>in Test Mode</strong>
          </span>
        </Chip>
      )}

      {hasOutstandingInvoice && <BillingWarningChip />}

      {showPaymentMethodProblemChip && (
        <div className={styles.paymentMethodProblemChipContainer}>
          <PaymentMethodProblemChip />
        </div>
      )}
    </div>
  )
}

export { SidebarAlerts }
