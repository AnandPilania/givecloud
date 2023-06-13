import { selector } from 'recoil'
import cardholderDataState from './cardholderData'
import formInputState from './formInput'

const billingAddress = selector({
  key: 'billingAddress',
  get: ({ get }) => {
    const formInput = get(formInputState)

    return {
      billing_address1: formInput.billing_address1,
      billing_address2: formInput.billing_address2,
      billing_city: formInput.billing_city,
      billing_province_code: formInput.billing_province_code,
      billing_zip: formInput.billing_zip,
      billing_country_code: formInput.billing_country_code,
    }
  },
  set: ({ get, set }, newValue) => {
    const formInput = get(formInputState)
    const cardholderData = get(cardholderDataState)

    set(formInputState, {
      ...formInput,
      billing_address1: newValue.billing_address1 || null,
      billing_address2: newValue.billing_address2 || null,
      billing_city: newValue.billing_city || null,
      billing_province_code: newValue.billing_province_code || null,
      billing_zip: newValue.billing_zip || null,
      billing_country_code: newValue.billing_country_code || null,
    })

    set(cardholderDataState, {
      ...cardholderData,
      address_line1: newValue.billing_address1 || null,
      address_line2: newValue.billing_address2 || null,
      address_city: newValue.billing_city || null,
      address_state: newValue.billing_province_code || null,
      address_zip: newValue.billing_zip || null,
      address_country: newValue.billing_country_code || null,
    })
  },
})

export default billingAddress
