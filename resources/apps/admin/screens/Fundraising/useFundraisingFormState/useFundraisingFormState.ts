import type { BrandingState } from '@/screens/Fundraising/VisualEditor/TemplateBrandingCard/LogoColourAccordion/logoColourState'
import { SCREEN, TAB } from '@/screens/Fundraising/VisualEditor/types'
import { useSetRecoilState, useRecoilValue, useResetRecoilState } from 'recoil'
import { fundraisingFormState } from './fundraisingFormState'
import {
  brandingState,
  colourErrorState,
} from '@/screens/Fundraising/VisualEditor/TemplateBrandingCard/LogoColourAccordion/logoColourState'
import {
  templateState,
  nameErrorState,
  templateBrandingErrorState,
} from '@/screens/Fundraising/VisualEditor/TemplateBrandingCard/NameTemplateAccordion/nameTemplateState'
import { layoutState, layoutErrorState } from '@/screens/Fundraising/VisualEditor/LayoutCard/layoutState'
import {
  defaultAmountState,
  defaultAmountErrorState,
  todayAndMonthlyState,
  transparencyPromiseState,
  socialProofState,
  transparencyPromiseErrorState,
  donationFieldsError,
} from '@/screens/Fundraising/VisualEditor/DonationCard/donationState'
import { remindersErrorState, remindersState } from '@/screens/Fundraising/VisualEditor/RemindersCard/remindersState'
import { doubleTheDonationState } from '@/screens/Fundraising/VisualEditor/EmployerMatchingCard/employerMatchingState'
import {
  emailOptinErrorState,
  emailOptInState,
} from '@/screens/Fundraising/VisualEditor/EmailOptinCard/emailOptinState'
import { thankYouState } from '@/screens/Fundraising/VisualEditor/ThankYouCard/thankYouState'
import { sharingErrorState, sharingState } from '@/screens/Fundraising/VisualEditor/SharingCard/sharingState'
import { emailErrorState, emailState } from '@/screens/Fundraising/VisualEditor/EmailCard/emailState'
import { upsellErrorState, upsellState } from '@/screens/Fundraising/VisualEditor/UpsellCard/upsellState'
import { integrationsState } from '@/screens/Fundraising/FundraisingFormDashboard/IntegrationsDialog/integrationsState'

type CurrentForm = Record<string, string>
interface Redirect {
  screen: string
  tab: string
  form: CurrentForm
}

const getLink = ({ screen, tab, form }: Redirect) => new URLSearchParams({ ...form, screen, tab }).toString()

const errorsNavigation = {
  template: {
    heading: 'Template & Branding',
    url: (form: CurrentForm) => getLink({ form, screen: SCREEN.DONATION, tab: TAB.TEMPLATE }),
  },
  layout: {
    heading: 'Layout',
    url: (form: CurrentForm) => getLink({ form, screen: SCREEN.DONATION, tab: TAB.LAYOUT }),
  },
  donation: {
    heading: 'Customize Experience: Donation',
    url: (form: CurrentForm) => getLink({ form, screen: SCREEN.DONATION, tab: TAB.EXPERIENCE }),
  },
  reminders: {
    heading: 'Customize Experience: Reminders',
    url: (form: CurrentForm) => getLink({ form, screen: SCREEN.REMINDER, tab: TAB.EXPERIENCE }),
  },
  upsell: {
    heading: 'Customize Experience: Upsell',
    url: (form: CurrentForm) => getLink({ form, screen: SCREEN.UPSELL, tab: TAB.EXPERIENCE }),
  },
  emailOptin: {
    heading: 'Customize Experience: Email Opt-In',
    url: (form: CurrentForm) => getLink({ form, screen: SCREEN.EMAIL_OPT_IN, tab: TAB.EXPERIENCE }),
  },
  sharing: {
    heading: 'Sharing & Page View',
    url: (form: CurrentForm) => getLink({ form, screen: SCREEN.DONATION, tab: TAB.SHARING }),
  },
  email: {
    heading: 'Email',
    url: (form: CurrentForm) => getLink({ form, screen: SCREEN.DONATION, tab: TAB.EMAIL }),
  },
} as const

type NavigationError = keyof typeof errorsNavigation

type NavigationErrorMap = typeof errorsNavigation

type ErrorData = {
  [key in NavigationError]: NavigationErrorMap[key]
}

export type Error = () => ErrorData[NavigationError]

const useFundraisingFormState = () => {
  const templateValue = useRecoilValue(templateState)
  const isNameError = useRecoilValue(nameErrorState)
  const isColourError = useRecoilValue(colourErrorState)
  const setTemplateState = useSetRecoilState(templateState)
  const brandingValue = useRecoilValue<BrandingState>(brandingState)
  const setBrandingState = useSetRecoilState(brandingState)
  const layoutValue = useRecoilValue(layoutState)
  const setLayoutState = useSetRecoilState(layoutState)
  const isLayoutError = useRecoilValue(layoutErrorState)
  const isTemplateBrandingError = useRecoilValue(templateBrandingErrorState)
  const defaultAmountValue = useRecoilValue(defaultAmountState)
  const { isCustomAmountValuesError, isDefaultAmountError } = useRecoilValue(defaultAmountErrorState)
  const isDonationFieldsError = useRecoilValue(donationFieldsError)
  const setDefaultAmountState = useSetRecoilState(defaultAmountState)
  const todayAndMonthlyValue = useRecoilValue(todayAndMonthlyState)
  const setTodayAndMonthlyState = useSetRecoilState(todayAndMonthlyState)
  const transparencyPromiseValue = useRecoilValue(transparencyPromiseState)
  const setTransparencyPromiseState = useSetRecoilState(transparencyPromiseState)
  const isTransparencyError = useRecoilValue(transparencyPromiseErrorState)
  const socialProofValue = useRecoilValue(socialProofState)
  const setSocialProofState = useSetRecoilState(socialProofState)
  const remindersValue = useRecoilValue(remindersState)
  const setRemindersState = useSetRecoilState(remindersState)
  const { isReminderError, isExitConfirmationDescriptionError, isOptionsReminderDescriptionError } =
    useRecoilValue(remindersErrorState)
  const upsellValue = useRecoilValue(upsellState)
  const setUpsellState = useSetRecoilState(upsellState)
  const isUpsellError = useRecoilValue(upsellErrorState)
  const doubleTheDonationValue = useRecoilValue(doubleTheDonationState)
  const setDoubleTheDonationState = useSetRecoilState(doubleTheDonationState)
  const emailOptinValue = useRecoilValue(emailOptInState)
  const setEmailOptInState = useSetRecoilState(emailOptInState)
  const isEmailOptinError = useRecoilValue(emailOptinErrorState)
  const thankYouValue = useRecoilValue(thankYouState)
  const setThankYouState = useSetRecoilState(thankYouState)
  const sharingValue = useRecoilValue(sharingState)
  const setSharingState = useSetRecoilState(sharingState)
  const isSharingError = useRecoilValue(sharingErrorState)
  const emailValue = useRecoilValue(emailState)
  const setEmailState = useSetRecoilState(emailState)
  const isEmailThankYouError = useRecoilValue(emailErrorState)
  const integrationsValue = useRecoilValue(integrationsState)
  const setIntegrationsState = useSetRecoilState(integrationsState)
  const fundraisingState = useRecoilValue(fundraisingFormState)
  const setFundraisingState = useSetRecoilState(fundraisingFormState)
  const resetFundraisingState = useResetRecoilState(fundraisingFormState)

  const title = fundraisingState.templateFields.name?.length
    ? fundraisingState.templateFields.name
    : 'New Fundraising Experience'

  const isFormValid =
    !isTemplateBrandingError &&
    !isNameError &&
    !isColourError &&
    !isLayoutError &&
    !isDonationFieldsError &&
    !isReminderError &&
    !isUpsellError &&
    !isEmailOptinError &&
    !isSharingError &&
    !isEmailThankYouError

  const errorsMap = {
    template: isNameError || isTemplateBrandingError || isColourError,
    layout: isLayoutError,
    donation: isDonationFieldsError,
    reminders: isReminderError,
    upsell: isUpsellError,
    emailOptin: isEmailOptinError,
    sharing: isSharingError,
    email: isEmailThankYouError,
  }

  const getError: Error = () => {
    for (const error in errorsMap) if (!!errorsMap[error]) return errorsNavigation[error]
  }

  return {
    templateValue,
    setTemplateState,
    brandingValue,
    setBrandingState,
    layoutValue,
    setLayoutState,
    defaultAmountValue,
    setDefaultAmountState,
    todayAndMonthlyValue,
    setTodayAndMonthlyState,
    transparencyPromiseValue,
    setTransparencyPromiseState,
    socialProofValue,
    setSocialProofState,
    remindersValue,
    setRemindersState,
    upsellValue,
    setUpsellState,
    doubleTheDonationValue,
    setDoubleTheDonationState,
    emailOptinValue,
    setEmailOptInState,
    thankYouValue,
    setThankYouState,
    sharingValue,
    setSharingState,
    emailValue,
    setEmailState,
    integrationsValue,
    setIntegrationsState,
    fundraisingState,
    setFundraisingState,
    resetFundraisingState,
    title,
    isNameError,
    isColourError,
    isLayoutError,
    isDonationFieldsError,
    isDefaultAmountError,
    isCustomAmountValuesError,
    isTransparencyError,
    isUpsellError,
    isEmailOptinError,
    isSharingError,
    isEmailThankYouError,
    isReminderError,
    isExitConfirmationDescriptionError,
    isOptionsReminderDescriptionError,
    isFormValid,
    getError,
  }
}

export { useFundraisingFormState }
