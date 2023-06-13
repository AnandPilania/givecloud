import { useState } from 'react'
import { useRecoilValue } from 'recoil'
import classnames from 'classnames'
import { delay } from 'nanodelay'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faHeart } from '@fortawesome/pro-regular-svg-icons'
import { faLock } from '@fortawesome/pro-solid-svg-icons'
import PropTypes from 'prop-types'
import Button from '@/components/Button/Button'
import Screen from '@/components/Screen/Screen'
import AreYouARobot from '@/screens/AreYouARobot/AreYouARobot'
import HerospaceIcon from '@/components/HerospaceIcon/HerospaceIcon'
import AddressInput from './components/AddressInput/AddressInput'
import CardholderNameInput from './components/CardholderNameInput'
import EmailAddressInput from './components/EmailAddressInput'
import useCheckout from '@/hooks/useCheckout'
import useLocalization from '@/hooks/useLocalization'
import useToastErrors from '@/hooks/useToastErrors'
import configState from '@/atoms/config'
import captchaState from '@/atoms/captcha'
import formInputState from '@/atoms/formInput'
import pendingContributionState from '@/atoms/pendingContribution'
import useCurrencyFormatter from '@/hooks/useCurrencyFormatter'
import useErrorBag from '@/hooks/useErrorBag'
import { isEmpty } from '@/utilities/helpers'
import styles from './CompleteCheckout.scss'

const requireFormInputs = [
  'billing_first_name',
  'billing_last_name',
  'billing_email',
  'billing_address1',
  'billing_city',
  'billing_province_code',
  'billing_zip',
  'billing_country_code',
]

const CompleteCheckout = (props) => {
  const t = useLocalization('screens.complete_checkout')

  const config = useRecoilValue(configState)
  const formInput = useRecoilValue(formInputState)
  const pendingContribution = useRecoilValue(pendingContributionState)
  const formatCurrency = useCurrencyFormatter({ showCurrencyCode: true })
  const attemptCheckout = useCheckout()

  const { errorBag } = useErrorBag()
  const toastError = useToastErrors()

  const captcha = useRecoilValue(captchaState)
  const [showCaptcha, setShowCaptcha] = useState(false)

  const showAddressInput = config.require_billing_address || formInput.payment_type === 'bank_account'

  const ignoredDisableGiveBtn =
    !!errorBag.length ||
    requireFormInputs.reduce((disableGiveBtn, key) => disableGiveBtn || isEmpty(formInput[key]), false)

  const handleCheckout = async (data = {}) => {
    try {
      await attemptCheckout(data)
    } catch (err) {
      toastError(err)

      // triggering validation... all to aware how horrifically gross doing this is
      // and this 100% makes me cry inside. complete refactor error/validation handling is on the books

      if (document.getElementById('placesAutocompleteInput')?.value) {
        document.getElementById('addressInputSwitchMode')?.click()
      }

      setTimeout(() => {
        const selectors = [
          '#placesAutocompleteInput',
          'input[name=email_address]',
          'input[name=cardholder_name]',
          'input[name=billing_address1]',
          'input[name=billing_city]',
          'select[name=billing_province_code]',
          'input[name=billing_zip]',
          'select[name=billing_country_code]',
        ]

        selectors.forEach((selector) => {
          const el = document.querySelector(selector)
          el?.focus()
          el?.blur()
        })
      }, 25)
    }
  }

  const handleGiveButton = () => {
    if (captcha.required) {
      setShowCaptcha(true)
    } else {
      handleCheckout()
    }
  }

  const handleCaptchaVerification = async (captchaResponse) => {
    // delaying in order to give the ReCAPTCHA checkmark
    // animation time to complete and help avoid an awkward transition
    await delay(400)
    await handleCheckout({ captcha_response: captchaResponse })

    setShowCaptcha(false)
  }

  return (
    <Screen className={styles.root} {...props}>
      <div className={styles.content}>
        <div className={classnames(styles.components)}>
          <HerospaceIcon icon={faHeart} controls={props.controls} />

          <h3>{t('heading')}</h3>
          <p>{t('description')}</p>

          <div className={styles.formContainer}>
            <div className={styles.formTitle}>{t('contact_info')}</div>
            <CardholderNameInput placeholder={t('first_and_last_name')} />
            <EmailAddressInput />
            {showAddressInput && <AddressInput />}
          </div>
        </div>

        {showCaptcha && <AreYouARobot onVerify={handleCaptchaVerification} />}

        <div className={styles.giveContainer}>
          <Button className={styles.giveButton} onClick={handleGiveButton}>
            <FontAwesomeIcon icon={faLock} /> {t('give')}{' '}
            {t(pendingContribution.is_monthly ? 'monthly_amount' : 'amount', {
              amount: formatCurrency(pendingContribution.total),
            })}
          </Button>
        </div>
      </div>
    </Screen>
  )
}

CompleteCheckout.propTypes = {
  controls: PropTypes.any,
}

export default CompleteCheckout
