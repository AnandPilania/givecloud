import { useEffect } from 'react'
import { useRecoilState, useSetRecoilState } from 'recoil'
import Givecloud from 'givecloud'
import cardBrandState from '@/atoms/cardBrand'
import hostedPaymentFieldsState from '@/atoms/hostedPaymentFields'
import useErrorBag from '@/hooks/useErrorBag'
import useLocalization from '@/hooks/useLocalization'

const numberId = 'inputPaymentNumber'
const expiryId = 'inputPaymentExpiry'
const cvvId = 'inputPaymentCVV'

const fontFamily = `Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Helvetica Neue,Arial,sans-serif`
const fontSize = '14px'

const cleanupFields = () => {
  document.getElementById(numberId)?.replaceChildren()
  document.getElementById(expiryId)?.replaceChildren()
  document.getElementById(cvvId)?.replaceChildren()
}

const setupBraintreeFields = (t, gateway) => {
  return setupPaysafeFields(t, gateway)
}

const setupPaysafeFields = (t, gateway) => {
  gateway.setupFields(
    {
      cardNumber: {
        selector: `#${numberId}`,
        placeholder: t('card_number'),
        separator: ' ',
      },
      expiryDate: {
        selector: `#${expiryId}`,
        placeholder: t('card_exp'),
      },
      cvv: {
        selector: `#${cvvId}`,
        placeholder: t('card_cvv'),
        optional: false,
      },
    },
    {
      input: {
        'font-family': fontFamily,
        'font-weight': 'normal',
        'font-size': fontSize,
      },
      'input::placeholder': {
        color: '#9ca3af',
      },
    },
    { cssSrc: 'https://fonts.googleapis.com/css?family=Inter' }
  )

  return cleanupFields
}

const setupStripeFields = (t, gateway) => {
  gateway.setupFields(
    {
      cardNumber: {
        selector: '#inputPaymentNumber',
        placeholder: t('card_number'),
        container: '#creditCardInput',
      },
      cardExpiry: {
        selector: '#inputPaymentExpiry',
        placeholder: t('card_exp'),
        container: '#creditCardInput',
      },
      cardCvc: {
        selector: '#inputPaymentCVV',
        placeholder: t('card_cvv'),
        container: '#creditCardInput',
      },
    },
    {
      base: {
        fontFamily,
        fontWeight: 'normal',
        fontSize,
        '::placeholder': {
          color: '#9ca3af',
        },
      },
      invalid: {
        color: '#eb1c26',
        '::placeholder': {
          color: '#fca5a5',
        },
      },
    },
    { fonts: [{ cssSrc: 'https://fonts.googleapis.com/css?family=Inter' }] },
    { followFocus: true }
  )

  return cleanupFields
}

const useHostedPaymentFields = () => {
  const t = useLocalization('screens.pay_with_credit_card')

  const setCardBrand = useSetRecoilState(cardBrandState)
  const [hostedPaymentFields, setHostedPaymentFields] = useRecoilState(hostedPaymentFieldsState)
  const { setError, setShouldValidate } = useErrorBag()

  const gateway = Givecloud.PaymentTypeGateway('credit_card')
  const usingHostedAchFields = gateway.usesAchHostedPaymentFields()
  const usingHostedCardFields = gateway.usesHostedPaymentFields()

  const setupHostedAchFields = () => {}

  const setupHostedCardFields = () => {
    if (!usingHostedCardFields || !document.getElementById(numberId)) {
      return
    }

    // prettier-ignore
    switch (gateway.$name) {
      case 'braintree': return setupBraintreeFields(t, gateway)
      case 'paysafe': return setupPaysafeFields(t, gateway)
      case 'stripe': return setupStripeFields(t, gateway)
    }
  }

  useEffect(() => {
    const onGcNumberType = (e) => setCardBrand(e.detail)

    const onGcHostedFieldChange = (e) => {
      if (e.detail.empty) {
        e.detail.error = t(`card_${e.detail.type}_required`)
      }

      // some of the integrations use the localization key for
      // the error message in which case we need to translate
      if (['card_number_invalid', 'card_exp_invalid', 'card_cvv_invalid'].includes(e.detail.error)) {
        e.detail.error = t(e.detail.error)
      }

      setError(`card_${e.detail.type}`, e.detail.error)
      setShouldValidate(`card_${e.detail.type}`)

      setHostedPaymentFields({ ...hostedPaymentFields, [e.detail.type]: e.detail })
    }

    document.addEventListener('gc-number-type', onGcNumberType)
    document.addEventListener('gc-hosted-field-change', onGcHostedFieldChange)

    return () => {
      document.removeEventListener('gc-number-type', onGcNumberType)
      document.removeEventListener('gc-hosted-field-change', onGcHostedFieldChange)
    }
  }, [hostedPaymentFields, setCardBrand, setError, setHostedPaymentFields, setShouldValidate, t])

  const validateCardFields = () => {
    if (!hostedPaymentFields.number) {
      setError('card_number', t('card_number_required'))
      return false
    }

    if (!hostedPaymentFields.exp) {
      setError('card_exp', t('card_exp_required'))
      return false
    }

    if (!hostedPaymentFields.cvv) {
      setError('card_cvv', t('card_cvv_required'))
      return false
    }

    return true
  }

  return {
    usingHostedAchFields,
    usingHostedCardFields,
    setupHostedAchFields,
    setupHostedCardFields,
    validateCardFields,
  }
}

export default useHostedPaymentFields
