import { selector, DefaultValue } from 'recoil'
import { templateState } from '@/screens/Fundraising/VisualEditor/TemplateBrandingCard/NameTemplateAccordion/nameTemplateState'
import { brandingState } from '@/screens/Fundraising/VisualEditor/TemplateBrandingCard/LogoColourAccordion/logoColourState'
import { layoutState } from '@/screens/Fundraising/VisualEditor/LayoutCard/layoutState'
import {
  defaultAmountState,
  todayAndMonthlyState,
  transparencyPromiseState,
  socialProofState,
} from '@/screens/Fundraising/VisualEditor/DonationCard/donationState'
import { integrationsState } from '@/screens/Fundraising/FundraisingFormDashboard/IntegrationsDialog/integrationsState'
import { remindersState } from '@/screens/Fundraising/VisualEditor/RemindersCard/remindersState'
import { upsellState } from '@/screens/Fundraising/VisualEditor/UpsellCard/upsellState'
import { doubleTheDonationState } from '@/screens/Fundraising/VisualEditor/EmployerMatchingCard/employerMatchingState'
import { emailOptInState } from '@/screens/Fundraising/VisualEditor/EmailOptinCard/emailOptinState'
import { thankYouState } from '@/screens/Fundraising/VisualEditor/ThankYouCard/thankYouState'
import { sharingState } from '@/screens/Fundraising/VisualEditor/SharingCard/sharingState'
import { emailState } from '@/screens/Fundraising/VisualEditor/EmailCard/emailState'

export const fundraisingFormState = selector({
  key: 'fundraisingFormState',
  get: ({ get }) => {
    const templateFields = get(templateState)
    const layoutFields = get(layoutState)
    const brandingFields = get(brandingState)
    const defaultAmountFields = get(defaultAmountState)
    const todayAndMonthlyFields = get(todayAndMonthlyState)
    const transparencyPromiseFields = get(transparencyPromiseState)
    const socialProofFields = get(socialProofState)
    const remindersFields = get(remindersState)
    const upsellFields = get(upsellState)
    const doubleTheDonationFields = get(doubleTheDonationState)
    const emailOptInFields = get(emailOptInState)
    const thankyouFields = get(thankYouState)
    const sharingFields = get(sharingState)
    const emailFields = get(emailState)
    const integrationsFields = get(integrationsState)

    return {
      templateFields,
      brandingFields,
      layoutFields,
      defaultAmountFields,
      todayAndMonthlyFields,
      transparencyPromiseFields,
      socialProofFields,
      remindersFields,
      upsellFields,
      doubleTheDonationFields,
      emailOptInFields,
      thankyouFields,
      sharingFields,
      emailFields,
      integrationsFields,
    }
  },
  set: ({ set }, value) => {
    if (value instanceof DefaultValue) {
      set(templateState, value)
      set(brandingState, value)
      set(layoutState, value)
      set(defaultAmountState, value)
      set(todayAndMonthlyState, value)
      set(transparencyPromiseState, value)
      set(socialProofState, value)
      set(remindersState, value)
      set(upsellState, value)
      set(doubleTheDonationState, value)
      set(emailOptInState, value)
      set(thankYouState, value)
      set(sharingState, value)
      set(emailState, value)
      set(integrationsState, value)
      return
    }
    const {
      templateFields,
      brandingFields,
      layoutFields,
      defaultAmountFields,
      todayAndMonthlyFields,
      transparencyPromiseFields,
      socialProofFields,
      remindersFields,
      upsellFields,
      doubleTheDonationFields,
      emailOptInFields,
      thankyouFields,
      sharingFields,
      emailFields,
      integrationsFields,
    } = value
    set(templateState, templateFields)
    set(brandingState, brandingFields)
    set(layoutState, layoutFields)
    set(defaultAmountState, defaultAmountFields)
    set(todayAndMonthlyState, todayAndMonthlyFields)
    set(transparencyPromiseState, transparencyPromiseFields)
    set(socialProofState, socialProofFields)
    set(remindersState, remindersFields)
    set(upsellState, upsellFields)
    set(doubleTheDonationState, doubleTheDonationFields)
    set(emailOptInState, emailOptInFields)
    set(thankYouState, thankyouFields)
    set(sharingState, sharingFields)
    set(emailState, emailFields)
    set(integrationsState, integrationsFields)
  },
})
