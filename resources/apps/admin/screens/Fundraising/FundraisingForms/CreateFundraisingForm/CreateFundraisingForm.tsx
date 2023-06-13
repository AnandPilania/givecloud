import type { FC, FormEvent } from 'react'
import type { BrandingState } from '@/screens/Fundraising/VisualEditor/TemplateBrandingCard/LogoColourAccordion/logoColourState'
import { useEffect } from 'react'
import { useHistory, useLocation } from 'react-router-dom'
import usePageTitle from '@/hooks/usePageTitle'
import { triggerToast } from '@/aerosol'
import { VisualEditor } from '@/screens/Fundraising/VisualEditor'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { useCreateFundraisingFormMutation } from './useCreateFundraisingFormMutation'

interface Props {
  isOpen: boolean
}

const CreateFundraisingForm: FC<Props> = ({ isOpen }) => {
  usePageTitle('Create a New Fundraising Experience')
  const history = useHistory()
  const { pathname } = useLocation()
  const { mutate, isLoading } = useCreateFundraisingFormMutation()
  const { fundraisingState, resetFundraisingState, isFormValid, getError } = useFundraisingFormState()

  useEffect(() => {
    resetFundraisingState()
  }, [isOpen])

  const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault()

    if (!isFormValid) {
      const { heading, url } = getError()
      triggerToast({
        type: 'error',
        header: `Error in ${heading}`,
        description: `Click on the view button to review your errors`,
        options: { containerId: 'visual-editor', autoClose: false, closeButton: false },
        buttonProps: {
          children: 'View',
          to: {
            pathname,
            search: url({ form: 'createFundraisingForm' }),
          },
        },
      })
    } else {
      mutate(
        {
          fundraisingForm: {
            name: fundraisingState.templateFields.name,
            template: fundraisingState.templateFields.template.type,
            brandingLogo: (fundraisingState.brandingFields as BrandingState).brandingLogo.id,
            brandingMonthlyLogo: (fundraisingState.brandingFields as BrandingState).brandingMonthlyLogo.id,
            brandingColour: (fundraisingState.brandingFields as BrandingState).brandingColour.code,
            layout: fundraisingState.layoutFields.layout,
            landingPageHeadline: fundraisingState.layoutFields.landingPageHeadline,
            landingPageDescription: fundraisingState.layoutFields.landingPageDescription,
            backgroundImage: fundraisingState.layoutFields.backgroundImage.id,
            socialProofEnabled: fundraisingState.socialProofFields.socialProofEnabled,
            socialProofPrivacy: fundraisingState.socialProofFields.socialProofPrivacy,
            billingPeriods: fundraisingState.todayAndMonthlyFields.billingPeriods,
            defaultAmountType: fundraisingState.defaultAmountFields.defaultAmountType,
            defaultAmountValue: fundraisingState.defaultAmountFields.defaultAmountValue,
            defaultAmountValues: fundraisingState.defaultAmountFields.defaultAmountValues.map(({ value }) => value),
            transparencyPromiseEnabled: fundraisingState.transparencyPromiseFields.transparencyPromiseEnabled,
            transparencyPromiseType: fundraisingState.transparencyPromiseFields.transparencyPromiseType,
            transparencyPromiseStatement: fundraisingState.transparencyPromiseFields.transparencyPromiseStatement,
            transparencyPromise1Percentage: Number(
              fundraisingState.transparencyPromiseFields.transparencyPromise1Percentage
            ),
            transparencyPromise1Description: fundraisingState.transparencyPromiseFields.transparencyPromise1Description,
            transparencyPromise2Percentage: Number(
              fundraisingState.transparencyPromiseFields.transparencyPromise2Percentage
            ),
            transparencyPromise2Description: fundraisingState.transparencyPromiseFields.transparencyPromise2Description,
            exitConfirmationDescription: fundraisingState.remindersFields.exitConfirmationDescription,
            embedOptionsReminderDescription: fundraisingState.remindersFields.embedOptionsReminderDescription,
            embedOptionsReminderEnabled: fundraisingState.remindersFields.embedOptionsReminderEnabled,
            embedOptionsReminderPosition: fundraisingState.remindersFields.embedOptionsReminderPosition,
            embedOptionsReminderBackgroundColour: fundraisingState.remindersFields.embedOptionsReminderBackgroundColour,
            upsellDescription: fundraisingState.upsellFields.upsellDescription,
            upsellEnabled: fundraisingState.upsellFields.upsellEnabled,
            doubleTheDonationEnabled: fundraisingState.doubleTheDonationFields.doubleTheDonationEnabled,
            emailOptinDescription: fundraisingState.emailOptInFields.emailOptinDescription,
            emailOptinEnabled: fundraisingState.emailOptInFields.emailOptinEnabled,
            thankYouOnscreenMessage: fundraisingState.thankyouFields.thankYouOnscreenMessage,
            socialLinkTitle: fundraisingState.sharingFields.socialLinkTitle,
            socialLinkDescription: fundraisingState.sharingFields.socialLinkDescription,
            socialPreviewImage: fundraisingState.sharingFields.socialPreviewImage.id,
            thankYouEmailMessage: fundraisingState.emailFields.thankYouEmailMessage,
            dpCampaign: fundraisingState.integrationsFields.dpCampaign,
            dpGlCode: fundraisingState.integrationsFields.dpGlCode,
            dpSolicitCode: fundraisingState.integrationsFields.dpSolicitCode,
            dpSubSolicitCode: fundraisingState.integrationsFields.dpSubSolicitCode,
            gtmContainerId: fundraisingState.integrationsFields.gtmContainerId,
            metaPixelId: fundraisingState.integrationsFields.metaPixelId,
          },
        },
        {
          onSuccess: ({ data }) =>
            triggerToast({
              type: 'success',
              header: `${data?.data?.name} Created!`,
              options: {
                containerId: 'visual-editor',
                onClose: () => history.push(`/fundraising/forms/${data?.data?.id}`),
              },
            }),
          onError: () =>
            triggerToast({
              type: 'error',
              header: `Sorry there was an error creating ${fundraisingState.templateFields.name}.`,
              options: {
                containerId: 'visual-editor',
                autoClose: false,
              },
            }),
        }
      )
    }
  }

  return <VisualEditor isOpen={isOpen} onSubmit={handleSubmit} isLoading={isLoading} />
}

export { CreateFundraisingForm }
