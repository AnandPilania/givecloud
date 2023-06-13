import { createContext, useState, useEffect } from 'react'
import PropTypes from 'prop-types'
import useCountrySubdivisions from '@/hooks/useCountrySubdivisions'
import useCountries from '@/hooks/useCountries'
import formatMoney from '@/utilities/formatMoney'
import getDefaultStep, { processSteps } from '@/utilities/getDefaultStep'
import prepareVariants from '@/utilities/prepareVariants'
import getDefaultVariant from '@/utilities/getDefaultVariant'
import getDefaultCurrency from '@/utilities/getDefaultCurrency'
import getEmptyBillingAddress from '@/utilities/getEmptyBillingAddress'
import getPaypalAvailability from '@/utilities/getPaypalAvailability'
import getDefaultRecurrenceWeekday from '@/utilities/getDefaultRecurrenceWeekday'
import { supportedPrimaryColors } from '@/constants/styleConstants'
import ExpiredPageError from '@/components/ExpiredPageError/ExpiredPageError'

import {
  validate,
  amountSchema,
  personalInfoSchema,
  addressSchema,
  paymentCreditCardSchema,
  paymentBankSchema,
} from '@/root/schema'

export const StoreContext = createContext(null)

const StoreProvider = ({
  Givecloud,
  product,
  variantUnitAmounts,
  showGoalProgress,
  goalCurrencyFormat,
  disclaimerText,
  accountTypes,
  currencies,
  emailOptinLabel,
  donorTitle,
  donorTitleOptions,
  coverCostsCheckoutDescription,
  paymentDayOptions,
  paymentWeekdayOptions,
  recurringFirstPaymentDefault,
  presetsOtherLabel,
  canCreateAccount,
  createAccountLabel,
  createAccountDescription,
  recaptchaSiteKey,
  returnState,
  returnError,
  theme = 'light',
  primaryColor,
  title,
  summary,
  children,
  variantDescriptionsJson,
}) => {
  const variants = prepareVariants(product.variants)
  const defaultVariant = getDefaultVariant(variants)
  const canCoverTheFees = product.cover_costs_enabled
  const captchaType = Givecloud.config.captcha_type || 'recaptcha'

  const [processStep, setProcessStep] = useState(getDefaultStep(returnState))
  const [isProcessingPayment, setIsProcessingPayment] = useState(false)
  const [donationAmount, setDonationAmount] = useState('')
  const [donationChosenPreset, setDonationChosenPreset] = useState('')
  const [paymentError, setPaymentError] = useState(null)
  const [formErrors, setFormErrors] = useState({})
  const [paymentCardDetails, setPaymentCardDetails] = useState({})
  const [bankAccountDetails, setBankAccountDetails] = useState({})
  const [coverFeeAmount, setCoverFeeAmount] = useState(0)
  const [coverFeeType, setCoverFeeType] = useState(Givecloud.config.processing_fees.using_ai ? 'more_costs' : null)
  const [isAnonymous, setIsAnonymous] = useState(false)
  const [chosenVariant, setChosenVariant] = useState(defaultVariant)
  const [cart, setCart] = useState(null)
  const [returnErrorMessage, setReturnErrorMessage] = useState(returnError)
  const [recaptchaResponse, setRecaptchaResponse] = useState(null)
  const [captchaRef, setCaptchaRef] = useState(null)

  const [chosenAccountType, setChosenAccountType] = useState(accountTypes.length ? accountTypes[0] : null)

  const [currentCurrency, setCurrentCurrency] = useState(getDefaultCurrency(Givecloud.config.currency.code, currencies))

  const [paymentMethod, setPaymentMethod] = useState(Givecloud.Gateway.getDefaultPaymentType())
  const [usingHostedPaymentFields, setUsingHostedPaymentFields] = useState(null)

  const [billingPeriod, setBillingPeriod] = useState('onetime')
  const [billingAddress, setBillingAddress] = useState(getEmptyBillingAddress(Givecloud.config.billing_country_code))

  const [isCoveringTheFees, setIsCoveringTheFees] = useState(
    canCoverTheFees ? Givecloud.config.processing_fees.cover : false
  )

  const [showOptionalFirstPaymentToday, setShowOptionalFirstPaymentToday] = useState(false)

  const [firstPaymentToday, setFirstPaymentToday] = useState(recurringFirstPaymentDefault == 1)

  const [isPaypalAvailable, setIsPaypalAvailable] = useState(
    getPaypalAvailability(Givecloud, chosenVariant.billing_period)
  )

  const [minimumDonation, setMinimumDonation] = useState(chosenVariant.minimum_price * currentCurrency.rate)

  const [recurrenceDay, setRecurrenceDay] = useState(
    paymentDayOptions.length ? paymentDayOptions[0] : paymentDayOptions
  )

  const [recurrenceWeekday, setRecurrenceWeekday] = useState(getDefaultRecurrenceWeekday(paymentWeekdayOptions))

  const [requireCaptcha, setRequireCaptcha] = useState(Givecloud.config.requires_captcha)

  const [billingCountrySubdivisionLabel, billingCountrySubdivisions] = useCountrySubdivisions(
    Givecloud,
    billingAddress.billing_country_code
  )

  const [countries] = useCountries()

  const creditCards = {
    'american-express': 'https://cdn.givecloud.co/npm/payment-icons@1.1.0/min/flat/amex.svg',
    'diners-club': 'https://cdn.givecloud.co/npm/payment-icons@1.1.0/min/flat/diners.svg',
    discover: 'https://cdn.givecloud.co/npm/payment-icons@1.1.0/min/flat/discover.svg',
    jcb: 'https://cdn.givecloud.co/npm/payment-icons@1.1.0/min/flat/jcb.svg',
    maestro: 'https://cdn.givecloud.co/npm/payment-icons@1.1.0/min/flat/maestro.svg',
    'master-card': 'https://cdn.givecloud.co/npm/payment-icons@1.1.0/min/flat/mastercard.svg',
    unionpay: 'https://cdn.givecloud.co/npm/payment-icons@1.1.0/min/flat/unionpay.svg',
    visa: 'https://cdn.givecloud.co/npm/payment-icons@1.1.0/min/flat/visa.svg',
  }

  const supportedCards = Object.keys(creditCards).filter((card) => {
    return Givecloud.config.supported_cardtypes.indexOf(card) !== -1
  })

  useEffect(() => {
    const billingPeriod = chosenVariant.billing_period
    const allow_first_payment_on_recurring = chosenVariant.allow_first_payment_on_recurring
    const showOptionalFirstPaymentToday = billingPeriod !== 'onetime' && allow_first_payment_on_recurring

    setShowOptionalFirstPaymentToday(showOptionalFirstPaymentToday)
    setIsPaypalAvailable(getPaypalAvailability(Givecloud, billingPeriod))
    setDonationAmount('')
    setDonationChosenPreset('')
    setBillingPeriod(billingPeriod)
  }, [chosenVariant, Givecloud])

  useEffect(() => {
    setMinimumDonation(chosenVariant.minimum_price * currentCurrency.rate)
  }, [chosenVariant, currentCurrency])

  useEffect(() => {
    setCoverFeeAmount(Givecloud.Dcc.getCost(parseFloat(donationAmount || 0), coverFeeType))
  }, [coverFeeType, donationAmount, Givecloud])

  const setBillingField = (name, value) => {
    setBillingAddress((details) => {
      return {
        ...details,
        [name]: value,
      }
    })
  }

  const validPrimaryColor = Object.prototype.hasOwnProperty.call(supportedPrimaryColors, primaryColor)
    ? primaryColor
    : 'indigo'

  const store = {
    api: Givecloud,
    theme,
    primaryColor: validPrimaryColor,
    title,
    summary,
    variantUnitAmounts,
    variantDescriptions: variantDescriptionsJson ? JSON.parse(variantDescriptionsJson) : {},
    showGoalProgress,
    goalCurrencyFormat,
    disclaimerText,
    processStep: {
      current: processStep,
      next: (input = {}, paymentInput = {}) => {
        setProcessStep((currentStep) => {
          // steps: ['amount', 'personal', 'payment', 'address', 'confirm'];
          const currentStepIndex = processSteps.indexOf(currentStep)
          let isValid = true

          if (currentStep === 'amount') {
            isValid = validate(amountSchema(minimumDonation), input, setFormErrors)
          } else if (currentStep === 'personal') {
            isValid = validate(personalInfoSchema, input, setFormErrors)
          } else if (currentStep === 'address') {
            isValid = validate(addressSchema, input, setFormErrors)
          } else if (currentStep === 'payment' && paymentMethod === 'credit_card' && !usingHostedPaymentFields) {
            isValid = validate(paymentCreditCardSchema, paymentInput, setFormErrors)
          } else if (currentStep === 'payment' && paymentMethod === 'bank_account') {
            isValid = validate(paymentBankSchema(currentCurrency.code), paymentInput, setFormErrors)
          }
          if (isValid && currentStepIndex < processSteps.length - 1) {
            return processSteps[currentStepIndex + 1]
          }
          return processSteps[currentStepIndex]
        })
      },
      previous: () => {
        setProcessStep((currentStep) => {
          const currentStepIndex = processSteps.indexOf(currentStep)

          if (currentStepIndex > 0) {
            return processSteps[currentStepIndex - 1]
          }

          return processSteps[0]
        })
      },
    },
    countries,
    donation: {
      value: donationAmount,
      totalWithFees: parseFloat(donationAmount) + (isCoveringTheFees ? coverFeeAmount : 0),
      set: setDonationAmount,
      preset: {
        chosen: donationChosenPreset,
        set: setDonationChosenPreset,
      },
    },
    cart: {
      value: cart,
      set: setCart,
    },
    formErrors: {
      all: formErrors,
      set: setFormErrors,
    },
    captcha: {
      key: recaptchaSiteKey,
      type: captchaType,
      required: requireCaptcha,
      setRequired: setRequireCaptcha,
      reset: () => {
        if (captchaType === 'hcaptcha') {
          captchaRef.current.resetCaptcha()
        } else if (captchaType === 'recaptcha') {
          captchaRef.current.reset()
        }

        setRecaptchaResponse(null)
      },
      response: {
        value: recaptchaResponse,
        set: setRecaptchaResponse,
      },
      ref: {
        value: captchaRef,
        set: setCaptchaRef,
      },
    },
    account: {
      create: {
        isAllowed: canCreateAccount,
        label: createAccountLabel,
        description: createAccountDescription,
      },
    },
    accountTypes: {
      all: accountTypes,
      chosen: chosenAccountType,
      set: setChosenAccountType,
    },
    donorTitle: {
      show: donorTitle !== 'hidden',
      required: donorTitle === 'required',
      all: donorTitleOptions,
    },
    anonymity: {
      value: isAnonymous,
      set: setIsAnonymous,
    },
    emailOptinLabel,
    product,
    variants: {
      all: variants,
      chosen: chosenVariant,
      set: setChosenVariant,
    },
    presetsOtherLabel,
    coverFees: {
      show: canCoverTheFees,
      value: isCoveringTheFees,
      set: setIsCoveringTheFees,
      label: coverCostsCheckoutDescription.replace('[$$$]', formatMoney(coverFeeAmount, currentCurrency.code)),
      amount: coverFeeAmount,
      type: coverFeeType,
      setType: setCoverFeeType,
    },
    currency: {
      all: currencies,
      chosen: currentCurrency,
      set: setCurrentCurrency,
    },
    billing: {
      details: billingAddress,
      set: setBillingAddress,
      setField: setBillingField,
      subdivisions: {
        label: billingCountrySubdivisionLabel,
        all: billingCountrySubdivisions,
      },
    },
    recurrence: {
      day: {
        options: paymentDayOptions,
        chosen: recurrenceDay,
        set: setRecurrenceDay,
      },
      weekday: {
        options: paymentWeekdayOptions,
        chosen: recurrenceWeekday,
        set: setRecurrenceWeekday,
      },
      scheduleType: product.recurring_schedule,
      firstPaymentToday: {
        value: firstPaymentToday,
        set: setFirstPaymentToday,
      },
      showOptionalFirstPaymentToday,
    },
    minimumDonation,
    payment: {
      processing: {
        isRightNow: isProcessingPayment,
        set: setIsProcessingPayment,
      },
      error: {
        value: paymentError,
        set: setPaymentError,
      },
      method: {
        chosen: paymentMethod,
        set: setPaymentMethod,
        isPaypalAvailable,
      },
      usingHostedPaymentFields: {
        value: usingHostedPaymentFields,
        set: setUsingHostedPaymentFields,
      },
      card: {
        details: {
          number: paymentCardDetails.number || '',
          exp_month: paymentCardDetails.exp_month || '',
          exp_year: paymentCardDetails.exp_year || '',
          cvv: paymentCardDetails.cvv || '',
          type: paymentCardDetails.type || '',
          save_payment_method: paymentCardDetails.save_payment_method || false,
        },
        set: setPaymentCardDetails,
      },
      bank: {
        details: {
          account_holder_type: bankAccountDetails.account_holder_type || '',
          account_type: bankAccountDetails.account_type || '',
          transit_number: bankAccountDetails.transit_number || '',
          institution_number: bankAccountDetails.institution_number || '',
          routing_number: bankAccountDetails.routing_number || '',
          account_number: bankAccountDetails.account_number || '',
          ach_agree_tos: bankAccountDetails.ach_agree_tos || false,
          save_payment_method: bankAccountDetails.save_payment_method || false,
        },
        set: setBankAccountDetails,
      },
    },
    creditCards: {
      logos: creditCards,
      supported: supportedCards,
    },
    returnError: {
      show: returnState === 'error' && !!returnErrorMessage,
      message: returnErrorMessage,
      set: setReturnErrorMessage,
    },
  }

  store.giveAgain = () => {
    setProcessStep(processSteps[0])
    setChosenVariant(defaultVariant)
    setDonationAmount('')
    setDonationChosenPreset('')
    setCart(null)
  }

  store.submissionInput = {
    account_type_id: store.accountTypes.chosen.id,
    currency_code: store.currency.chosen.id,
    payment_type: store.payment.method.chosen,
    billing_title: store.billing.details.billing_title,
    billing_first_name: store.billing.details.billing_first_name,
    billing_last_name: store.billing.details.billing_last_name,
    billing_company: store.billing.details.billing_company,
    billing_email: store.billing.details.billing_email,
    email_opt_in: store.billing.details.email_opt_in,
    billing_address1: store.billing.details.billing_address1,
    billing_address2: store.billing.details.billing_address2,
    billing_city: store.billing.details.billing_city,
    billing_province_code: store.billing.details.billing_province_code,
    billing_zip: store.billing.details.billing_zip,
    billing_country_code: store.billing.details.billing_country_code,
    billing_phone: store.billing.details.billing_phone,
    cover_costs_enabled: store.coverFees.value,
    cover_costs_type: store.coverFees.type,
    is_anonymous: store.anonymity.value,
    recaptcha_response: store.captcha.response.value,
    comments: '',
    item: {
      recurring_frequency: billingPeriod === 'onetime' ? null : billingPeriod,
      recurring_with_initial_charge: store.recurrence.firstPaymentToday.value,
      variant_id: store.variants.chosen.id,
      currency_code: store.currency.chosen.code,
      recurring_day: store.recurrence.day.chosen,
      recurring_day_of_week: store.recurrence.weekday.chosen,
      amt: store.donation.value,
      gift_aid: false,
      form_fields: {},
    },
  }

  store.paymentInput = {
    method: store.payment.method.chosen,
  }

  if (store.payment.method.chosen === 'credit_card') {
    store.paymentInput.number = store.payment.card.details.number
    store.paymentInput.number_type = store.payment.card.details.type
    store.paymentInput.cvv = store.payment.card.details.cvv
    store.paymentInput.exp_month = store.payment.card.details.exp_month
    store.paymentInput.exp_year = store.payment.card.details.exp_year
    store.paymentInput.save_payment_method = store.payment.card.details.save_payment_method
  } else if (store.payment.method.chosen === 'bank_account') {
    store.paymentInput.account_holder_type = store.payment.bank.details.account_holder_type
    store.paymentInput.account_type = store.payment.bank.details.account_type
    store.paymentInput.transit_number = store.payment.bank.details.transit_number
    store.paymentInput.institution_number = store.payment.bank.details.institution_number
    store.paymentInput.routing_number = store.payment.bank.details.routing_number
    store.paymentInput.account_number = store.payment.bank.details.account_number
    store.paymentInput.ach_agree_tos = store.payment.bank.details.ach_agree_tos
    store.paymentInput.save_payment_method = store.payment.bank.details.save_payment_method
  }

  const addPaymentDetails = (paymentData, cart) => {
    paymentData.name = cart.billing_address.name
    paymentData.address_line1 = cart.billing_address.address1
    paymentData.address_line2 = cart.billing_address.address2
    paymentData.address_city = cart.billing_address.city
    paymentData.address_state = cart.billing_address.province_code
    paymentData.address_zip = cart.billing_address.zip
    paymentData.address_country = cart.billing_address.country_code

    return paymentData
  }

  store.submitDonation = async () => {
    const gateway = store.api.PaymentTypeGateway(store.payment.method.chosen)

    try {
      store.payment.processing.set(true)

      const data = await store.api.Cart.oneClickCheckout(
        store.submissionInput,
        store.cart.value,
        store.payment.method.chosen
      )

      store.cart.set(data.cart)

      if (data.cart.requires_payment) {
        const paymentData = addPaymentDetails(store.paymentInput, data.cart)

        const token = await gateway.getCaptureToken(
          data.cart,
          paymentData,
          store.payment.method.chosen,
          store.submissionInput.recaptcha_response,
          paymentData.save_payment_method
        )

        await gateway.chargeCaptureToken(data.cart, token)
      } else {
        await store.api.Cart(data.cart.id).complete()
      }

      store.returnError.set(null)
      store.payment.processing.set(false)
      store.payment.error.set(null)
      store.processStep.next()
    } catch (err) {
      store.payment.processing.set(false)

      if (err?.data?.captcha) {
        if (store.captcha.required) {
          store.payment.error.set({
            message: 'Please click the box to confirm you are a human.',
          })
        } else {
          store.captcha.setRequired(true)
        }
      } else {
        if (err?.message?.includes('CSRF')) {
          store.payment.error.set({
            message: <ExpiredPageError />,
          })
        } else {
          store.payment.error.set(err)
        }
      }
    }
  }

  return <StoreContext.Provider value={store}>{children}</StoreContext.Provider>
}

StoreProvider.propTypes = {
  Givecloud: PropTypes.shape({
    config: PropTypes.shape({
      currency: PropTypes.shape({
        code: PropTypes.string,
      }),
      processing_fees: PropTypes.shape({
        cover: PropTypes.bool,
        rate: PropTypes.number,
        amount: PropTypes.number,
        using_ai: PropTypes.bool,
        costs_by_amounts: PropTypes.object,
      }),
      billing_country_code: PropTypes.string,
      supported_cardtypes: PropTypes.array,
      requires_captcha: PropTypes.bool,
      captcha_type: PropTypes.string,
    }),
    Dcc: PropTypes.shape({
      getCost: PropTypes.func,
    }),
    Gateway: PropTypes.func,
  }).isRequired,
  product: PropTypes.shape({
    variants: PropTypes.array,
    cover_costs_enabled: PropTypes.bool,
    recurring_schedule: PropTypes.string,
  }).isRequired,
  variantUnitAmounts: PropTypes.array,
  showGoalProgress: PropTypes.bool,
  goalCurrencyFormat: PropTypes.string,
  disclaimerText: PropTypes.string,
  accountTypes: PropTypes.array,
  currencies: PropTypes.array,
  emailOptinLabel: PropTypes.string,
  donorTitle: PropTypes.string,
  donorTitleOptions: PropTypes.array,
  coverCostsCheckoutDescription: PropTypes.string,
  paymentDayOptions: PropTypes.array,
  paymentWeekdayOptions: PropTypes.object,
  recurringFirstPaymentDefault: PropTypes.string,
  presetsOtherLabel: PropTypes.string,
  canCreateAccount: PropTypes.bool,
  createAccountLabel: PropTypes.string,
  createAccountDescription: PropTypes.string,
  recaptchaSiteKey: PropTypes.string,
  returnState: PropTypes.string,
  returnError: PropTypes.string,
  theme: PropTypes.oneOf(['light', 'dark']),
  primaryColor: PropTypes.string,
  title: PropTypes.string,
  summary: PropTypes.string,
  children: PropTypes.node,
  variantDescriptionsJson: PropTypes.string,
}

export default StoreProvider
