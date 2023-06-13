const getEmptyBillingAddress = (defaultCountryCode) => {
  return {
    billing_title: '',
    billing_first_name: '',
    billing_last_name: '',
    billing_company: '',
    billing_email: '',
    email_opt_in: false,
    billing_address1: '',
    billing_address2: '',
    billing_city: '',
    billing_province_code: '',
    billing_zip: '',
    billing_country_code: defaultCountryCode,
    billing_phone: '',
  }
}

export default getEmptyBillingAddress
