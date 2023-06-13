require('iframe-resizer')
import { memo } from 'react'
import qs from 'qs'
import StoreProvider from '@/root/store'
import DonateForm from '@/components/DonateForm/DonateForm'

const Givecloud = window.Givecloud
const el = document.getElementById('app')

const product = JSON.parse(el.getAttribute('data-product'))
const variantUnitAmounts = JSON.parse(el.getAttribute('data-variant-unit-amounts'))
const goalCurrencyFormat = el.getAttribute('data-goal-currency-format')
const disclaimerText = el.getAttribute('data-disclaimer-text')
const accountTypes = JSON.parse(el.getAttribute('data-account-types'))
const emailOptinLabel = JSON.parse(el.getAttribute('data-email-optin-label'))
const canCreateAccount = JSON.parse(el.getAttribute('data-can-create-account')) == 1 ? true : false
const createAccountLabel = JSON.parse(el.getAttribute('data-create-account-label'))
const createAccountDescription = JSON.parse(el.getAttribute('data-create-account-description'))
const donorTitle = JSON.parse(el.getAttribute('data-donor-title'))
const donorTitleOptions = JSON.parse(el.getAttribute('data-donor-title-options'))
const coverCostsCheckoutDescription = JSON.parse(el.getAttribute('data-cover-costs-checkout-description'))
const currencies = JSON.parse(el.getAttribute('data-currencies'))
const recurringFirstPaymentDefault = JSON.parse(el.getAttribute('data-recurring-first-payment-default'))
const presetsOtherLabel = JSON.parse(el.getAttribute('data-product-preset-other-label'))
const paymentDayOptions = JSON.parse(el.getAttribute('data-payment-day-options'))
const paymentWeekdayOptions = JSON.parse(el.getAttribute('data-payment-weekday-options'))
const recaptchaSiteKey = JSON.parse(el.getAttribute('data-recaptcha-site-key'))
/*
    The following two inputs are for payment methods that need to refresh the
    whole page when completed. This is where the state of the transaction comes
    back in.
    returnState = 'thankyou' or 'error'
    returnError = the error message
*/
const returnState = el.getAttribute('data-return-state')
const returnError = el.getAttribute('data-return-error')

const queryString = window.location.search

const {
  theme,
  primaryColor,
  title,
  summary,
  showGoalProgress,
  variantDescriptions: variantDescriptionsJson,
} = qs.parse(queryString, {
  ignoreQueryPrefix: true,
})

/*

    - our is going to make a landing page which will redirect

    Todo:
    - get it into the product
    - comments
    - social sharing links

    - showing better what the different amounts represent
    - Better Summary
    - preload ticker with 130,000
    - countdown clock (to the event and until the end of the event)

    Note: Only donations can be used with this app (not products).
    Implications:
        - Price doesn't matter
        - Sales don't matter
        - Shipping doesn't matter

    Note: Other features we're ignoring for now
        - Accounts (logged in / my payment methods)
        - Redirects (not sure we'll ever include this in embed-able forms)
        - Tributes
        - Custom Fields
        - Pledges
        - Referral Source
        - Email Optin is ALWAYS ON
        - GoCardLess (bank)
        - Gift Aid

    Improvements
    - Address
        - Only ask for postal?
        - Google Address Fill in
    - Better Payment Options
        - Apple Pay
        - Google Pay
    - Email magic link to login
        - Should email be the second step in the process so that we can ask them to login and give them their payment methods?

*/

const App = () => (
  <StoreProvider
    Givecloud={Givecloud}
    product={product}
    variantUnitAmounts={variantUnitAmounts}
    showGoalProgress={!!showGoalProgress}
    goalCurrencyFormat={goalCurrencyFormat}
    disclaimerText={disclaimerText}
    currencies={currencies}
    accountTypes={accountTypes}
    emailOptinLabel={emailOptinLabel}
    donorTitle={donorTitle}
    donorTitleOptions={donorTitleOptions}
    coverCostsCheckoutDescription={coverCostsCheckoutDescription}
    paymentDayOptions={paymentDayOptions}
    paymentWeekdayOptions={paymentWeekdayOptions}
    canCreateAccount={canCreateAccount}
    createAccountLabel={createAccountLabel}
    createAccountDescription={createAccountDescription}
    recurringFirstPaymentDefault={recurringFirstPaymentDefault}
    presetsOtherLabel={presetsOtherLabel}
    recaptchaSiteKey={recaptchaSiteKey}
    returnState={returnState}
    returnError={returnError}
    theme={theme}
    primaryColor={primaryColor}
    title={title}
    summary={summary}
    variantDescriptionsJson={variantDescriptionsJson}
  >
    <DonateForm />
  </StoreProvider>
)

export default memo(App)
