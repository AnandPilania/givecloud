import type { FC, FormEvent } from 'react'
import type { BrandingState } from '@/screens/Fundraising/VisualEditor/TemplateBrandingCard/LogoColourAccordion/logoColourState'
import { useEffect } from 'react'
import { useLocation, useParams } from 'react-router-dom'
import { useRecoilValue } from 'recoil'
import { triggerToast } from '@/aerosol'
import configState from '@/atoms/config'
import { templates } from '@/screens/Fundraising/VisualEditor/TemplatesCard/templates'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { VisualEditor } from '@/screens/Fundraising/VisualEditor'
import { useUpdateFundraisingFormMutation } from './useUpdateFundraisingFormMutation'
import { useFundraisingFormQuery } from './useFundraisingFormQuery'
import { getThemeColour } from '@/shared/utilities'

interface Props {
  isOpen: boolean
}
interface IDParam {
  id: string
}

interface ConfigState {
  isFundraisingFormsStandardLayoutEnabled: boolean
}

const getTemplate = (name: string) => templates.find((template) => name === template.type) ?? templates[0]

const UpdateFundraisingForm: FC<Props> = ({ isOpen }) => {
  const { id } = useParams<IDParam>()
  const { pathname } = useLocation()
  const { isFundraisingFormsStandardLayoutEnabled } = useRecoilValue<ConfigState>(configState)
  const { fundraisingState, setFundraisingState, isFormValid, getError } = useFundraisingFormState()
  const { mutate, isLoading } = useUpdateFundraisingFormMutation(id)
  const { data, isError } = useFundraisingFormQuery({
    id,
    options: {
      enabled: !!id,
    },
  })

  useEffect(() => {
    if (data && isOpen) {
      setFundraisingState({
        templateFields: {
          template: getTemplate(data?.template),
          name: data?.name,
        },
        brandingFields: {
          brandingLogo: {
            id: data?.brandingLogo?.id,
            full: data?.brandingLogo?.full,
          },
          brandingMonthlyLogo: {
            id: data?.brandingMonthlyLogo?.id,
            full: data?.brandingMonthlyLogo?.full,
          },
          brandingColour: getThemeColour(data?.brandingColour),
        },
        layoutFields: {
          layout: data?.layout ?? (isFundraisingFormsStandardLayoutEnabled ? 'standard' : 'simplified'),
          landingPageHeadline: data?.landingPageHeadline ?? '',
          landingPageDescription: data?.landingPageDescription ?? '',
          backgroundImage: {
            id: data?.backgroundImage?.id,
            full: data?.backgroundImage?.full,
          },
        },
        socialProofFields: {
          socialProofEnabled: data?.socialProofEnabled,
          socialProofPrivacy: data?.socialProofPrivacy,
        },
        todayAndMonthlyFields: {
          billingPeriods: data?.billingPeriods,
        },
        defaultAmountFields: {
          defaultAmountType: data?.defaultAmountType,
          defaultAmountValue: data?.defaultAmountValue,
          defaultAmountValues: [
            {
              name: 'inputOne',
              value: data?.defaultAmountValues?.[0] ?? 45,
              errors: [],
            },
            {
              name: 'inputTwo',
              value: data?.defaultAmountValues?.[1] ?? 95,
              errors: [],
            },
            {
              name: 'inputThree',
              value: data?.defaultAmountValues?.[2] ?? 150,
              errors: [],
            },
            {
              name: 'inputFour',
              value: data?.defaultAmountValues?.[3] ?? 250,
              errors: [],
            },
            {
              name: 'inputFive',
              value: data?.defaultAmountValues?.[4] ?? 500,
              errors: [],
            },
          ],
        },
        transparencyPromiseFields: {
          transparencyPromiseEnabled: data?.transparencyPromiseEnabled,
          transparencyPromiseType: data?.transparencyPromiseType,
          transparencyPromiseStatement: data?.transparencyPromiseStatement ?? '',
          transparencyPromise1Percentage: data?.transparencyPromise1Percentage,
          transparencyPromise1Description: data?.transparencyPromise1Description ?? '',
          transparencyPromise2Percentage: data?.transparencyPromise2Percentage,
          transparencyPromise2Description: data?.transparencyPromise2Description ?? '',
        },
        remindersFields: {
          exitConfirmationDescription: data?.exitConfirmationDescription ?? '',
          embedOptionsReminderDescription: data?.embedOptionsReminderDescription ?? '',
          embedOptionsReminderEnabled: data?.embedOptionsReminderEnabled,
          embedOptionsReminderPosition: data?.embedOptionsReminderPosition,
          embedOptionsReminderBackgroundColour: data?.embedOptionsReminderBackgroundColour ?? '#2467CC',
        },
        upsellFields: {
          upsellDescription: data?.upsellDescription ?? '',
          upsellEnabled: data?.upsellEnabled,
        },
        doubleTheDonationFields: {
          doubleTheDonationConnected: data?.doubleTheDonationConnected,
          doubleTheDonationEnabled: data?.doubleTheDonationEnabled,
        },
        emailOptInFields: {
          emailOptinDescription: data?.emailOptinDescription ?? '',
          emailOptinEnabled: data?.emailOptinEnabled,
        },
        thankyouFields: {
          thankYouOnscreenMessage: data?.thankYouOnscreenMessage ?? '',
        },
        sharingFields: {
          socialLinkTitle: data?.socialLinkTitle ?? '',
          socialLinkDescription: data?.socialLinkDescription ?? '',
          socialPreviewImage: {
            id: data?.socialPreviewImage?.id,
            full: data?.socialPreviewImage?.full,
          },
        },
        emailFields: {
          thankYouEmailMessage: data?.thankYouEmailMessage,
        },
        integrationsFields: {
          dpCampaign: data?.dpCampaign ?? '',
          dpGlCode: data?.dpGlCode ?? '',
          dpSolicitCode: data?.dpSolicitCode ?? '',
          dpSubSolicitCode: data?.dpSubSolicitCode ?? '',
          gtmContainerId: data?.gtmContainerId ?? '',
          metaPixelId: data?.metaPixelId ?? '',
        },
      })
    }
  }, [data, isOpen])

  if (isError) {
    triggerToast({
      type: 'error',
      header: `Sorry, there was an error loading the experience ${fundraisingState.templateFields.name}.`,
      description: 'Please refresh the page and try again.',
      options: { containerId: 'visual-editor', autoClose: false },
    })
  }

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
            search: url({ form: 'updateFundraisingForm' }),
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
            socialPreviewImage: fundraisingState.sharingFields.socialPreviewImage?.id,
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
          onSuccess: ({ data }) => {
            triggerToast({
              type: 'success',
              header: `${data?.data?.name} Saved!`,
              options: { containerId: 'visual-editor' },
            })
          },
          onError: () => {
            triggerToast({
              type: 'error',
              header: `Sorry there was an error updating ${fundraisingState.templateFields.name}.`,
              options: { containerId: 'visual-editor', autoClose: false },
            })
          },
        }
      )
    }
  }

  return <VisualEditor isOpen={isOpen} onSubmit={handleSubmit} isLoading={isLoading} />
}

export { UpdateFundraisingForm }
