import { useCallback } from 'react'
import { useRecoilState, useRecoilValue, useResetRecoilState, useSetRecoilState } from 'recoil'
import { clone, cloneDeep } from 'lodash'
import Givecloud from 'givecloud'
import captchaState from '@/atoms/captcha'
import cardholderDataState from '@/atoms/cardholderData'
import contributionState from '@/atoms/contribution'
import configState from '@/atoms/config'
import formInputState from '@/atoms/formInput'
import paymentFailureState from '@/atoms/paymentFailure'
import paymentStatusState from '@/atoms/paymentStatus'
import pendingContributionState from '@/atoms/pendingContribution'

let processingLock = false

const useCheckout = () => {
  const config = useRecoilValue(configState)
  const formInput = useRecoilValue(formInputState)
  const cardholderData = useRecoilValue(cardholderDataState)
  const pendingContribution = useRecoilValue(pendingContributionState)

  const [captcha, setCaptcha] = useRecoilState(captchaState)
  const [contribution, setContribution] = useRecoilState(contributionState)
  const setPaymentFailure = useSetRecoilState(paymentFailureState)
  const setPaymentStatus = useSetRecoilState(paymentStatusState)

  const resetCaptchaState = useResetRecoilState(captchaState)
  const resetCardholderDataState = useResetRecoilState(cardholderDataState)
  const resetFormInputState = useResetRecoilState(formInputState)

  const attemptCheckout = useCallback(
    async (formInputData) => {
      const inputData = {
        ...cloneDeep(formInput),
        ...formInputData,
      }

      const paymentData = {
        ...clone(cardholderData),
        wallet_pay: null,
      }

      const gateway = Givecloud.PaymentTypeGateway(inputData.payment_type)

      if (inputData.payment_type === 'wallet_pay') {
        setPaymentStatus('using_wallet')
        paymentData.wallet_pay = await gateway.getWalletPayToken(
          pendingContribution.total,
          pendingContribution.currency_code,
          inputData.wallet_type
        )
      }

      setPaymentStatus('processing')

      if (inputData.payment_type === 'paypal') {
        setPaymentStatus('using_paypal')
        gateway.openPayPalCheckoutPopupWindow()
      }

      let { cart } = await Givecloud.Cart.oneClickCheckout(
        inputData,
        contribution,
        inputData.payment_type,
        config.require_billing_address || inputData.payment_type === 'bank_account'
      )

      // Moving forward we are always saving the payment method to
      // facilitate upgrades, covering fees after the fact, etc.
      const token = await gateway.getCaptureToken(
        cart,
        paymentData,
        inputData.payment_type,
        inputData.captcha_response || captcha.response,
        gateway.canSavePaymentMethods()
      )

      setPaymentStatus('processing')

      // prettier-ignore
      ;({ cart } = await gateway.chargeCaptureToken(cart, token))

      setContribution(cart)

      resetCaptchaState()
      resetCardholderDataState()
      resetFormInputState()

      setPaymentStatus('approved')
    },
    [
      captcha.response,
      cardholderData,
      config.require_billing_address,
      contribution,
      formInput,
      pendingContribution.currency_code,
      pendingContribution.total,
      resetCaptchaState,
      resetCardholderDataState,
      resetFormInputState,
      setContribution,
      setPaymentStatus,
    ]
  )

  return useCallback(
    async (formInputData = {}) => {
      if (processingLock) {
        return
      }

      try {
        processingLock = true
        await attemptCheckout(formInputData)
      } catch (err) {
        if (err?.data?.captcha) {
          if (captcha.required) {
            captcha?.reset?.()
          } else {
            setCaptcha({ ...captcha, required: true })
          }
        }

        if (err?.data?.cart) {
          setContribution(err.data.cart)
        }

        if (err?.data?.payment_failure) {
          setPaymentFailure(err.data.payment_failure)
          setPaymentStatus('declined')
        } else {
          const cardErrors = [
            // braintree
            'All fields are empty. Cannot tokenize empty card fields.',
            'Some payment input fields are invalid. Cannot tokenize invalid card fields.',

            // paysafe
            'Invalid fields: card number,cvv,expiry date.',
            'Invalid fields: card number,expiry date.',
            'Invalid fields: card number.',
            'Invalid fields: cvv,expiry date.',
            'Invalid fields: cvv.',
            'Invalid fields: expiry date.',

            // givecloud
            'Error: Credit card number is required.',
          ]

          let hasCardError = cardErrors.reduce((hasError, value) => hasError || String(err) === value, false)

          // handle validation errors as thrown by stripe
          if (err?.error?.type === 'validation_error') {
            hasCardError = true
          }

          if (hasCardError) {
            window.G_navigateTo('pay_with_credit_card', 'POP', 'set')
          }

          setPaymentStatus(null)
          throw err
        }
      } finally {
        processingLock = false
      }
    },
    [attemptCheckout, captcha, setCaptcha, setContribution, setPaymentFailure, setPaymentStatus]
  )
}

export default useCheckout
