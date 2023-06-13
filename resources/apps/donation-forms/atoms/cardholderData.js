import { atom } from 'recoil'
import getConfig from '@/utilities/config'

const config = getConfig()

// @see resources/apps/core/src/cardholder-data.js
const cardholderData = atom({
  key: 'cardholderData',
  default: {
    name: null,
    address_line1: null,
    address_line2: null,
    address_city: null,
    address_state: null,
    address_zip: null,
    address_country: null,
    number: null,
    brand: null,
    cvv: null,
    exp: null,
    country: null,
    currency: config.local_currency.code,
    transit_number: null,
    institution_number: null,
    routing_number: null,
    account_number: null,
    account_type: null,
    account_holder_name: null,
    account_holder_type: null,
    wallet_pay: null,
  },
})

export default cardholderData
