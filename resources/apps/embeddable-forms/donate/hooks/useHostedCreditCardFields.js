import { useContext, useEffect } from 'react'
import { useEffectOnce } from 'react-use'
import Givecloud from 'givecloud'
import { StoreContext } from '@/root/store'

const numberId = 'inputPaymentNumber'
const expiryId = 'inputPaymentExpiry'
const cvvId = 'inputPaymentCVV'

const fontFamily = `Helvetica Neue,Helvetica,Arial,sans-serif`
const fontSize = '16px'

const cleanupFields = () => {
  document.getElementById(numberId)?.replaceChildren()
  document.getElementById(expiryId)?.replaceChildren()
  document.getElementById(cvvId)?.replaceChildren()
}

const setupBraintreeFields = (gateway) => {
  return setupPaysafeFields(gateway)
}

const setupPaysafeFields = (gateway) => {
  gateway.setupFields(
    {
      cardNumber: {
        selector: `#${numberId}`,
        placeholder: '0000 0000 0000 0000',
        separator: ' ',
      },
      expiryDate: {
        selector: `#${expiryId}`,
        placeholder: 'MM / YY',
      },
      cvv: {
        selector: `#${cvvId}`,
        placeholder: '000',
        optional: false,
      },
    },
    {
      input: {
        'font-family': fontFamily,
        'font-weight': 'normal',
        'font-size': fontSize,
      },
    }
  )

  return cleanupFields
}

const setupStripeFields = (gateway) => {
  gateway.setupFields(
    {
      cardNumber: {
        selector: `#${numberId}`,
        placeholder: '0000 0000 0000 0000',
        container: '#creditCardInput',
      },
      cardExpiry: {
        selector: `#${expiryId}`,
        placeholder: 'MM / YY',
        container: '#creditCardInput',
      },
      cardCvc: {
        selector: `#${cvvId}`,
        placeholder: '000',
        container: '#creditCardInput',
      },
    },
    {
      base: {
        fontFamily,
        fontWeight: 'normal',
        fontSize,
        lineHeight: '1.5',
      },
    }
  )

  return cleanupFields
}

const useHostedCreditCardFields = () => {
  const { payment } = useContext(StoreContext)

  const gateway = Givecloud.PaymentTypeGateway('credit_card')
  const usingHostedPaymentFields = gateway.usesHostedPaymentFields()

  useEffectOnce(() => {
    payment.usingHostedPaymentFields.set(usingHostedPaymentFields)

    if (!usingHostedPaymentFields) {
      return
    }

    // prettier-ignore
    switch (gateway.$name) {
      case 'braintree': return setupBraintreeFields(gateway)
      case 'paysafe': return setupPaysafeFields(gateway)
      case 'stripe': return setupStripeFields(gateway)
    }
  })

  useEffect(() => {
    const onGcNumberType = (e) => payment.card.set({ ...payment.card.details, type: e.detail })

    document.addEventListener('gc-number-type', onGcNumberType)
    return () => document.removeEventListener('gc-number-type', onGcNumberType)
  }, [payment.card])

  return usingHostedPaymentFields
}

export default useHostedCreditCardFields
