import { string, object, number, boolean } from 'yup' // for only what you need

export const validate = (schema, input, setFormErrors) => {
  try {
    schema.validateSync(input, { abortEarly: false })
    setFormErrors({})
    return true
  } catch (err) {
    const errors = {}
    err.inner.map((error) => {
      errors[error.path] = error.message
    })
    setFormErrors(errors)
  }
  return false
}

export const amountSchema = (minimumDonation) => {
  return object().shape({
    item: object({
      amt: number().required().positive().min(minimumDonation),
    }),
  })
}

export const personalInfoSchema = object().shape({
  account_type_id: number().required().positive().integer(),
  billing_first_name: string().required('We need your first name to continue'),
  billing_last_name: string().required('We need your last name to continue'),
  billing_email: string().email('We need your email to continue').required('We need your email to continue'),
  billing_phone: string().required('We need your phone number to continue'),
})

export const addressSchema = object().shape({
  billing_address1: string().required('Please enter your address'),
  billing_city: string().required('Please enter your city'),
  billing_province_code: string().required('Please enter your state / province'),
  billing_zip: string().required('Please enter your zip/postal code'),
  billing_country_code: string().required('Please enter your country'),
})

const minYear = new Date().getFullYear().toString().substr(-2)

export const paymentCreditCardSchema = object().shape({
  number: string().required('Please enter your credit card number').max(19),
  cvv: string().max(4).required('Please enter your credit card cvv'),
  exp_month: number('Please enter a valid credit card expiry month')
    .positive('Please enter a valid credit card expiry month')
    .min(1, 'Please enter a valid credit card expiry month')
    .max(12, 'Please enter a valid credit card expiry month')
    .required('Please enter your credit card expiry month'),
  exp_year: number('Please enter a valid credit card expiry year')
    .positive('Please enter a valid credit card expiry year')
    .min(minYear, 'Please enter a valid credit card expiry year')
    .max(99, 'Please enter a valid credit card expiry year')
    .required('Please enter your credit card expiry year'),
})

export const paymentBankSchema = (currency_code) => {
  let transitNumberValidator = string()
  let institutionNumberValidator = string()
  let routingNumberValidator = string()
  if (currency_code === 'CAD') {
    transitNumberValidator = transitNumberValidator.required('Please enter the transit number')
    institutionNumberValidator = institutionNumberValidator.required('Please enter the institution number')
  } else {
    routingNumberValidator = routingNumberValidator.required('Please enter the routing number')
  }

  return object().shape({
    account_holder_type: string().required('Please enter the account holder type'),
    account_type: string().required('Please enter the account type'),
    transit_number: transitNumberValidator,
    institution_number: institutionNumberValidator,
    routing_number: routingNumberValidator,
    account_number: string().required('Please enter the account number'),
    ach_agree_tos: boolean('Must Accept Terms and Conditions').oneOf([true], 'Must Accept Terms and Conditions'),
    save_payment_method: string(),
  })
}
