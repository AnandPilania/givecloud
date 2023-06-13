import { memo, useContext, useEffect } from 'react'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import CurrencySelector from '@/components/CurrencySelector/CurrencySelector'
import ProductDescription from '@/components/ProductDescription/ProductDescription'
import GoalProgress from '@/components/GoalProgress/GoalProgress'
import VariantSelector from '@/components/VariantSelector/VariantSelector'
import AmountSelector from '@/components/AmountSelector/AmountSelector'
import RecurrenceSelector from '@/components/RecurrenceSelector/RecurrenceSelector'
import FirstPaymentChoice from '@/components/FirstPaymentChoice/FirstPaymentChoice'
import PersonalInformation from '@/components/PersonalInformation/PersonalInformation'
import PaymentInformation from '@/components/PaymentInformation/PaymentInformation'
import Address from '@/components/Address/Address'
import Summary from '@/components/Summary/Summary'
import Thanks from '@/components/Thanks/Thanks'
import ProcessingWaitScreen from '@/components/ProcessingWaitScreen/ProcessingWaitScreen'
import ReturnError from '@/components/ReturnError/ReturnError'
import PreviousStepButton from '@/components/PreviousStepButton/PreviousStepButton'
import NextStepButton from '@/components/NextStepButton/NextStepButton'
import styles from '@/components/DonateForm/DonateForm.scss'

const DonateForm = () => {
  const { variants, disclaimerText, processStep, payment, donation, theme } = useContext(StoreContext)
  const isLightTheme = theme === 'light'

  useEffect(() => {
    const intervalId = setInterval(() => window.Givecloud.CsrfToken.check(), 600000)

    return () => clearInterval(intervalId)
  })

  if (variants.all.length === 0) {
    return 'There are no active donation forms.'
  }

  return (
    <div className={classnames(styles.root, !isLightTheme && styles.darkTheme)}>
      {/* Hidden element that contains the donation amount for testing purposes */}
      <span id='donationValue' className={styles.hiddenDonationValue} data-donation-value={donation?.value} />

      {/* To fix: the pb-10 class above is to make sure the iframe resizer doesnt cutoff the content */}
      <div className={styles.form}>
        {processStep.current === 'amount' && (
          <>
            <ReturnError />
            <ProductDescription />
            <GoalProgress />
            <VariantSelector />
            <CurrencySelector />
            <AmountSelector />
            <RecurrenceSelector />
            <FirstPaymentChoice />
            {disclaimerText && <div className={styles.disclaimerText}>{disclaimerText}</div>}
          </>
        )}

        {/* TODO: Add ability to create account */}
        {processStep.current === 'personal' && <PersonalInformation />}

        {/* Keep in DOM for tokenization during confirm step */}
        {['payment', 'address', 'confirm'].includes(processStep.current) && (
          <PaymentInformation className={classnames(processStep.current !== 'payment' && 'sr-only')} />
        )}

        {processStep.current === 'address' && <Address />}

        {payment.processing.isRightNow ? (
          <ProcessingWaitScreen />
        ) : (
          <>
            {processStep.current === 'confirm' && <Summary />}

            <div className={styles.stepButtonsContainer}>
              {processStep.current !== 'amount' && processStep.current !== 'thanks' && <PreviousStepButton />}
              {processStep.current !== 'confirm' && processStep.current !== 'thanks' && <NextStepButton />}
            </div>

            {processStep.current === 'thanks' && <Thanks />}
          </>
        )}
      </div>
    </div>
  )
}

export default memo(DonateForm)
