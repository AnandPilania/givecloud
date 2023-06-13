import { createRef, memo } from 'react'
import { useEffectOnce } from 'react-use'
import { useRecoilState, useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCreditCardFront } from '@fortawesome/pro-regular-svg-icons'
import Givecloud from 'givecloud'
import cardBrandState from '@/atoms/cardBrand'
import cardholderDataState from '@/atoms/cardholderData'
import Input from '@/components/Input/Input'
import NumberInput from './components/NumberInput'
import ExpiryInput from './components/ExpiryInput'
import CvvInput from './components/CvvInput'
import useErrorBag from '@/hooks/useErrorBag'
import useLocalization from '@/hooks/useLocalization'
import { isEmpty } from '@/utilities/helpers'
import AmericanExpressIcon from './images/AmericanExpress.svg?react'
import DinersClubIcon from './images/DinersClub.svg?react'
import DiscoverIcon from './images/Discover.svg?react'
import JCBIcon from './images/JCB.svg?react'
import MasterCardIcon from './images/MasterCard.svg?react'
import VisaIcon from './images/Visa.svg?react'
import styles from './CreditCardInput.scss'

const cardBrandIcons = {
  'american-express': AmericanExpressIcon,
  'diners-club': DinersClubIcon,
  discover: DiscoverIcon,
  jcb: JCBIcon,
  'master-card': MasterCardIcon,
  visa: VisaIcon,
}

const CreditCardInput = ({ usingHostedPaymentFields, setupHostedPaymentFields }) => {
  const t = useLocalization('screens.pay_with_credit_card')

  const cardholderData = useRecoilValue(cardholderDataState)
  const [cardBrand, setCardBrand] = useRecoilState(cardBrandState)
  const { errorBag, setError, shouldValidateBag, setShouldValidate } = useErrorBag()

  useEffectOnce(() => setupHostedPaymentFields())

  const CardBrandIcon = cardBrandIcons[cardBrand]
  const showCardBrandIcon = !!CardBrandIcon
  const showDefaultCardIcon = !showCardBrandIcon

  const numberInputRef = createRef()
  const expiryInputRef = createRef()
  const cvvInputRef = createRef()

  // prettier-ignore
  const isValid = !!(
    shouldValidateBag.card_number && !errorBag.card_number &&
    shouldValidateBag.card_exp && !errorBag.card_exp &&
    shouldValidateBag.card_cvv && !errorBag.card_cvv
  )

  const errorMessage = errorBag.card_number || errorBag.card_exp || errorBag.card_cvv

  const focusNumberInput = () => numberInputRef?.current?.focus()
  const focusExpiryInput = () => expiryInputRef?.current?.focus()
  const focusCvvInput = () => cvvInputRef?.current?.focus()

  const checkValidity = (value, e, shouldValidate) => {
    const field = `card_${e.target.name}`

    if (!shouldValidate) {
      return setError(field)
    }

    const validators = {
      card_number: () => Givecloud.CardholderData.validNumber(value),
      card_exp: () => Givecloud.CardholderData.validExpirationDate(value),
      card_cvv: () => {
        const cardType = Givecloud.CardholderData.getNumberType(cardholderData.number)
        return Givecloud.CardholderData.validCvv(value, cardType)
      },
    }

    if (isEmpty(value)) {
      return setError(field, t(`${field}_required`))
    }

    if (validators[field]()) {
      return setError(field)
    }

    return setError(field, t(`${field}_invalid`))
  }

  return (
    <Input error={errorMessage} validator={checkValidity}>
      {({ errorMessage, handleOnBlur: onBlur, handleOnChange }) => {
        const handleOnBlur = (e) => {
          setShouldValidate(`card_${e.target.name}`)
          onBlur(e)
        }

        const creditCardInputClasses = classnames(
          styles.root,
          usingHostedPaymentFields && styles.hostedPaymentFields,
          isValid && styles.valid,
          errorMessage && 'has-errors'
        )

        return (
          <div id='creditCardInput' className={creditCardInputClasses}>
            <div className={styles.iconContainer}>
              {showCardBrandIcon && (
                <div className={styles.cardBrandIcon}>
                  <CardBrandIcon />
                </div>
              )}

              {showDefaultCardIcon && <FontAwesomeIcon className={styles.icon} icon={faCreditCardFront} />}
            </div>

            <NumberInput
              ref={numberInputRef}
              className={styles.numberInput}
              setCardBrand={setCardBrand}
              focusExpiryInput={focusExpiryInput}
              usingHostedPaymentFields={usingHostedPaymentFields}
              onBlur={handleOnBlur}
              onChange={handleOnChange}
            />

            <ExpiryInput
              ref={expiryInputRef}
              className={styles.expiryInput}
              focusCvvInput={focusCvvInput}
              focusNumberInput={focusNumberInput}
              usingHostedPaymentFields={usingHostedPaymentFields}
              onBlur={handleOnBlur}
              onChange={handleOnChange}
            />

            <CvvInput
              ref={cvvInputRef}
              className={styles.cvvInput}
              focusExpiryInput={focusExpiryInput}
              usingHostedPaymentFields={usingHostedPaymentFields}
              onBlur={handleOnBlur}
              onChange={handleOnChange}
            />
          </div>
        )
      }}
    </Input>
  )
}

CreditCardInput.propTypes = {
  usingHostedPaymentFields: PropTypes.bool.isRequired,
  setupHostedPaymentFields: PropTypes.func.isRequired,
}

export default memo(CreditCardInput)
