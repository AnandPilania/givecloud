import { memo, useEffect, useRef, useState } from 'react'
import { useRecoilState, useRecoilValue } from 'recoil'
import { motion, useAnimation } from 'framer-motion'
import { FocusTrap } from '@headlessui/react'
import PropTypes from 'prop-types'
import { uniqueId } from 'lodash'
import Button from '@/components/Button/Button'
import Portal from '@/components/Portal/Portal'
import AccountNumberInput from './components/AccountNumberInput'
import AccountHolderTypeInput from './components/AccountHolderTypeInput'
import AccountTypeInput from './components/AccountTypeInput'
import MandateInput from './components/MandateInput'
import RoutingNumberInput from './components/RoutingNumberInput'
import useLocalization from '@/hooks/useLocalization'
import bankAccountState from '@/atoms/bankAccount'
import cardholderDataState from '@/atoms/cardholderData'
import styles from './AchBankAccountDialog.scss'

const AchBankAccountDialog = ({ showDialog, dismissDialog, usingHostedPaymentFields, setupHostedPaymentFields }) => {
  const t = useLocalization('screens.pay_with_credit_card')

  const bankAccount = useRecoilValue(bankAccountState)
  const [cardholderData, setCardholderData] = useRecoilState(cardholderDataState)
  const [dialogKey, setDialogKey] = useState(uniqueId('ach-bank-account-dialog-'))
  const focusTrapFeatures = showDialog ? 30 : 1

  const animation = useAnimation()
  const initialFocusRef = useRef(null)

  // using animation controls with display block/none instead of AnimatePresence in
  // order to keep the dialog in the DOM. when using hosted payment fields removing from
  // the DOM breaks the ability for the inputs to be tokenized later on
  useEffect(() => {
    animation.start(showDialog ? 'show' : 'hide')
  }, [animation, showDialog])

  useEffect(() => setupHostedPaymentFields(), [dialogKey, setupHostedPaymentFields])

  const handleOnContinue = () => {
    setCardholderData({ ...cardholderData, ...bankAccount })
    dismissDialog()
  }

  const backdropVariants = {
    hide: {
      opacity: 0,
      transitionEnd: {
        display: 'none',
      },
    },
    show: {
      opacity: 1,
      display: 'block',
    },
  }

  const dialogVariants = {
    hide: { y: '-100vh', opacity: 0 },
    show: {
      y: 0,
      opacity: 1,
      transition: { delay: 0.2 },
    },
  }

  const onAnimationComplete = () => {
    if (showDialog) {
      initialFocusRef?.current?.focus()
    } else {
      // changing key to force reset of both child components
      // important to do this was since hosted payment fields are DOM based
      setDialogKey(uniqueId('ach-bank-account-dialog-'))
    }
  }

  return (
    <Portal key={dialogKey}>
      <FocusTrap initialFocus={initialFocusRef} features={focusTrapFeatures}>
        <motion.div
          className={styles.root}
          variants={backdropVariants}
          initial='hide'
          animate={animation}
          exit='hide'
          onAnimationComplete={onAnimationComplete}
        >
          <motion.div
            role='dialog'
            aria-modal={true}
            aria-labelledby='achBankAccountDialogLabel'
            className={styles.dialog}
            variants={dialogVariants}
          >
            <div className={styles.content}>
              <h1 id='achBankAccountDialogLabel'>{t('bank_transfer')}</h1>

              <AccountTypeInput />
              <RoutingNumberInput usingHostedPaymentFields={usingHostedPaymentFields} ref={initialFocusRef} />
              <AccountNumberInput usingHostedPaymentFields={usingHostedPaymentFields} />
              <AccountHolderTypeInput />
              <MandateInput />
            </div>

            <div className={styles.controls}>
              <Button onClick={handleOnContinue}>{t('continue')}</Button>
            </div>
          </motion.div>
        </motion.div>
      </FocusTrap>
    </Portal>
  )
}

AchBankAccountDialog.propTypes = {
  showDialog: PropTypes.bool.isRequired,
  dismissDialog: PropTypes.func.isRequired,
  usingHostedPaymentFields: PropTypes.bool.isRequired,
  setupHostedPaymentFields: PropTypes.func.isRequired,
}

export default memo(AchBankAccountDialog)
