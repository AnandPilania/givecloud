import { atom } from 'recoil'
import Givecloud from 'givecloud'
import { getConfig, getStateFromPreviousVisit } from '@/utilities/config'
import { getURLParamValue } from '@/utilities/url'

const config = getConfig()
const stateFromPreviousVisit = getStateFromPreviousVisit()
const defaultVariant = config.variants.find((variant) => variant.is_default)

const getAmountFromURL = () => {
  const value = getURLParamValue('gc-a')
  const currencyValue = Number(value).toFixed(2)

  if (!value) return null
  return Math.max(5, Math.min(currencyValue, 50000))
}

const formInput = atom({
  key: 'formInput',
  default: {
    cart_id: null,
    account_type_id: 1,
    currency_code: config.local_currency.code,
    payment_type: Givecloud.Gateway.getDefaultPaymentType(),
    billing_title: null,
    billing_first_name: null,
    billing_last_name: null,
    billing_company: null,
    billing_email: null,
    email_opt_in: false,
    billing_address1: null,
    billing_address2: null,
    billing_city: null,
    billing_province_code: null,
    billing_zip: null,
    billing_country_code: config.local_country,
    billing_phone: null,
    ship_to_billing: null,
    shipping_method: null,
    shipping_title: null,
    shipping_first_name: null,
    shipping_last_name: null,
    shipping_company: null,
    shipping_email: null,
    shipping_address1: null,
    shipping_address2: null,
    shipping_city: null,
    shipping_province_code: null,
    shipping_zip: null,
    shipping_country_code: null,
    shipping_phone: null,
    password: null,
    cover_costs_enabled: config.cover_costs.using_ai ? !!config.cover_costs.default_type : true,
    cover_costs_type: config.cover_costs.default_type,
    is_anonymous: false,
    comments: '',
    referral_source: null,
    item: {
      amt: getAmountFromURL() || stateFromPreviousVisit?.amount || config.default_amount,
      variant_id: defaultVariant.id,
      recurring_frequency: defaultVariant.billing_period === 'onetime' ? null : defaultVariant.billing_period,
      recurring_with_initial_charge: true,
      fundraising_page_id: config.fundraising_page_id,
      fundraising_member_id: config.fundraising_member_id,
      form_fields: {},
    },
    items: [],
    utm_source: null,
    utm_medium: null,
    utm_campaign: null,
    utm_term: null,
    utm_content: null,
  },
})

export default formInput
