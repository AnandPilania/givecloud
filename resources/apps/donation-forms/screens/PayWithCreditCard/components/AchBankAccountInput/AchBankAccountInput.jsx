import { memo, useState } from 'react'
import { useRecoilValue } from 'recoil'
import classnames from 'classnames'
import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faUniversity } from '@fortawesome/pro-regular-svg-icons'
import useLocalization from '@/hooks/useLocalization'
import AchBankAccountDialog from './components/AchBankAccountDialog/AchBankAccountDialog'
import bankAccountState from '@/atoms/bankAccount'
import formInputState from '@/atoms/formInput'
import AmericanFlagIcon from './images/AmericanFlag.svg?react'
import CanadianFlagIcon from './images/CanadianFlag.svg?react'
import styles from './AchBankAccountInput.scss'

const AchBankAccountInput = ({ usingHostedPaymentFields, setupHostedPaymentFields }) => {
  const t = useLocalization('screens.pay_with_credit_card')

  const formInput = useRecoilValue(formInputState)
  const bankAccount = useRecoilValue(bankAccountState)
  const [showDialog, setShowDialog] = useState(false)

  const isCurrencyCad = formInput.currency_code === 'CAD'
  const labelTextLocalizationKey = isCurrencyCad ? 'ach_placeholder_cad' : 'ach_placeholder_usd'

  const isValid =
    ((isCurrencyCad && !!bankAccount.transit_number && !!bankAccount.institution_number) ||
      !!bankAccount.routing_number) &&
    !!bankAccount.mandate_accepted

  const showBankAccountSummaryText =
    (isCurrencyCad && (!!bankAccount.transit_number || !!bankAccount.institution_number)) ||
    !!bankAccount.routing_number ||
    !!bankAccount.account_number

  const bankAccountSummaryText = () => {
    const accountLastFour = (bankAccount.account_number || '').toString().slice(-4)
    const accountNumber = accountLastFour ? `*****${accountLastFour}` : '——————'

    if (isCurrencyCad) {
      const transitNumber = bankAccount.transit_number || '————'
      const institutionNumber = bankAccount.institution_number || '——'
      return `${transitNumber} | ${institutionNumber} | ${accountNumber}`
    }

    const routingNumber = bankAccount.routing_number || '—————'
    return `${routingNumber} | ${accountNumber}`
  }

  const dismissDialog = () => {
    // TODO: cleanup so setTimeout not required
    setTimeout(() => setShowDialog(false))
  }

  return (
    <div className={styles.root}>
      <button
        className={classnames(
          styles.input,
          showBankAccountSummaryText && isValid && styles.valid,
          showBankAccountSummaryText && !isValid && styles.invalid
        )}
        onClick={() => setShowDialog(true)}
      >
        <div className={styles.iconContainer}>
          <FontAwesomeIcon className={styles.icon} icon={faUniversity} />
        </div>

        {showBankAccountSummaryText && <div className={styles.summaryText}>{bankAccountSummaryText()}</div>}
        {!showBankAccountSummaryText && <div className={styles.labelText}>{t(labelTextLocalizationKey)}</div>}

        <div
          className={classnames(
            styles.flagIcon,
            isCurrencyCad ? styles.canadian : styles.american,
            showBankAccountSummaryText && !isValid && styles.invalid,
            !showBankAccountSummaryText && styles.grayscale
          )}
        >
          {isCurrencyCad ? <CanadianFlagIcon /> : <AmericanFlagIcon />}
        </div>
      </button>

      {showBankAccountSummaryText && !isValid && (
        <span className={styles.errorMessage}>
          {t(
            bankAccount.mandate_accepted
              ? isCurrencyCad
                ? 'routing_number_cad_required'
                : 'routing_number_required'
              : 'must_accept_mandate'
          )}
        </span>
      )}

      <AchBankAccountDialog
        showDialog={showDialog}
        dismissDialog={dismissDialog}
        usingHostedPaymentFields={usingHostedPaymentFields}
        setupHostedPaymentFields={setupHostedPaymentFields}
      />
    </div>
  )
}

AchBankAccountInput.propTypes = {
  usingHostedPaymentFields: PropTypes.bool.isRequired,
  setupHostedPaymentFields: PropTypes.func.isRequired,
}

export default memo(AchBankAccountInput)
