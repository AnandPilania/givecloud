import { useEffect } from 'react'
import { useRecoilValue, useResetRecoilState } from 'recoil'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faShieldCheck, faArrowRight } from '@fortawesome/pro-solid-svg-icons'
import Button from '@/components/Button/Button'
import Screen from '@/components/Screen/Screen'
import AchBankAccountInput from './components/AchBankAccountInput/AchBankAccountInput'
import CreditCardInput from './components/CreditCardInput/CreditCardInput'
import HerospaceIcon from '@/components/HerospaceIcon/HerospaceIcon'
import useAnalytics from '@/hooks/useAnalytics'
import useLocalization from '@/hooks/useLocalization'
import useAchBankAccount from './hooks/useAchBankAccount'
import useErrorBag from '@/hooks/useErrorBag'
import useCreditCard from './hooks/useCreditCard'
import useHostedPaymentFields from './hooks/useHostedPaymentFields'
import cardholderDataState from '@/atoms/cardholderData'
import formInputState from '@/atoms/formInput'
import hostedPaymentFieldsState from '@/atoms/hostedPaymentFields'
import cardBrandState from '@/atoms/cardBrand'
import { errorBag as errorBagState, shouldValidateBag as shouldValidateBagState } from '@/atoms/errorBag'
import styles from './PayWithCreditCard.scss'

const PayWithCreditCard = ({ navigateTo, ...unhandledProps }) => {
  const t = useLocalization('screens.pay_with_credit_card')

  const { setError, setShouldValidate } = useErrorBag()
  const cardholderData = useRecoilValue(cardholderDataState)
  const formInput = useRecoilValue(formInputState)
  const collectEvent = useAnalytics({ collectOnce: true })

  const {
    usingHostedAchFields,
    usingHostedCardFields,
    setupHostedAchFields,
    setupHostedCardFields,
    validateCardFields,
  } = useHostedPaymentFields()

  const { usingAchBankAccount, hasInvalidAchBankAccount } = useAchBankAccount(usingHostedAchFields)
  const { usingCreditCard, hasInvalidCreditCard } = useCreditCard(usingHostedCardFields)

  const resetCardholderDataState = useResetRecoilState(cardholderDataState)
  const resetErrorBag = useResetRecoilState(errorBagState)
  const resetShouldValidateBg = useResetRecoilState(shouldValidateBagState)
  const resetHostedPaymentFieldsState = useResetRecoilState(hostedPaymentFieldsState)
  const resetCardBrandState = useResetRecoilState(cardBrandState)

  useEffect(() => {
    resetCardholderDataState()
    resetErrorBag()
    resetShouldValidateBg()
    resetHostedPaymentFieldsState()
    resetCardBrandState()
  }, [
    resetCardholderDataState,
    resetErrorBag,
    resetShouldValidateBg,
    resetHostedPaymentFieldsState,
    resetCardBrandState,
  ])

  const disableContinueBtn =
    (usingAchBankAccount && hasInvalidAchBankAccount) || (usingCreditCard && hasInvalidCreditCard)

  const handleContinueButton = () => {
    const hasInvalidCardFields = usingCreditCard && !usingHostedCardFields && !cardholderData.number
    const hasInvalidHostedCardFields = usingCreditCard && usingHostedCardFields && !validateCardFields()

    if (hasInvalidCardFields) {
      setShouldValidate('card_number')
      setError('card_number', t('card_number_required'))
    }

    if (hasInvalidCardFields || hasInvalidHostedCardFields) {
      return
    }

    navigateTo('complete_checkout')
    collectEvent({ event_name: 'contact_info_view' })
  }

  collectEvent({ event_name: `${formInput.payment_type}_checkout_view` })

  return (
    <Screen className={styles.root} {...unhandledProps}>
      <div className={styles.content}>
        <div className={classnames(styles.components)}>
          <HerospaceIcon icon={faShieldCheck} />

          <h3>{t('heading')}</h3>
          <p>{t('description')}</p>

          <div className={styles.formContainer}>
            {usingAchBankAccount && (
              <>
                <div className={styles.formTitle}>{t('bank_ach')}</div>
                <AchBankAccountInput
                  usingHostedPaymentFields={usingHostedAchFields}
                  setupHostedPaymentFields={setupHostedAchFields}
                />
              </>
            )}
            {usingCreditCard && (
              <>
                <div className={styles.formTitle}>{t('credit_card')}</div>
                <CreditCardInput
                  usingHostedPaymentFields={usingHostedCardFields}
                  setupHostedPaymentFields={setupHostedCardFields}
                />
              </>
            )}
          </div>
        </div>

        <div className={styles.continueContainer}>
          <Button className={styles.continueButton} onClick={handleContinueButton} disabled={disableContinueBtn}>
            {t('continue')} <FontAwesomeIcon icon={faArrowRight} />
          </Button>
        </div>
      </div>
    </Screen>
  )
}

PayWithCreditCard.displayName = 'CheckoutWithCreditCard'

PayWithCreditCard.propTypes = {
  navigateTo: PropTypes.func.isRequired,
}

export default PayWithCreditCard
