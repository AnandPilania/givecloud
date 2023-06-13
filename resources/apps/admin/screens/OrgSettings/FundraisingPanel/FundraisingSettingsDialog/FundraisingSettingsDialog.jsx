import { useEffect, useState } from 'react'
import PropTypes from 'prop-types'
import {
  Button,
  Column,
  Columns,
  Container,
  Input,
  Dialog,
  DialogHeader,
  Text,
  TextArea,
  triggerToast,
  EmailInput,
  PhoneInput,
} from '@/aerosol'
import { DonationMethods } from './DonationMethods'
import { useFundraisingSettingsState } from '@/screens/OrgSettings/FundraisingPanel/useFundraisingSettingsState'
import { useUpdateFundraisingSettingsMutation } from './useUpdateFundraisingSettingsMutation'

const FundraisingSettingsDialog = ({ isOpen, onClose }) => {
  const { fundraisingValue, setFundraisingValue } = useFundraisingSettingsState()
  const [currentFundraisingState, setCurrentFundraisingState] = useState(fundraisingValue)
  const { mutate, isLoading, isSuccess, reset } = useUpdateFundraisingSettingsMutation()

  useEffect(() => {
    reset()
  }, [isOpen])

  const handleClose = () => {
    if (!isSuccess) setFundraisingValue(currentFundraisingState)
    onClose()
  }

  const handleSubmit = (e) => {
    e.preventDefault()
    if (isLoading) return null

    const nonEmptyWaysToDonate = fundraisingValue.orgOtherWaysToDonate.filter(
      (item) => item.label != '' && item.href != ''
    )

    mutate(
      {
        ...fundraisingValue,
        orgOtherWaysToDonate: nonEmptyWaysToDonate,
      },
      {
        onSuccess: ({ data: { data: response } }) => {
          triggerToast({
            type: 'success',
            header: 'Fundraising settings updated!',
            options: { containerId: 'fundraising-settings' },
          })
          setCurrentFundraisingState(response)
        },
        onError: () => {
          triggerToast({
            type: 'error',
            header: `Sorry there was an error updating your settings.`,
            options: { containerId: 'fundraising-settings' },
          })
        },
      }
    )
  }

  const handleChange = ({ target: { name, value } }) =>
    setFundraisingValue({
      ...fundraisingValue,
      [name]: value,
    })

  const handlePhoneChange = ({ country, phoneNumber }) => {
    setFundraisingValue({
      ...fundraisingValue,
      orgSupportNumberCountryCode: country.code,
      orgSupportNumber: phoneNumber,
    })
  }

  const staticContent = (
    <DialogHeader onClose={handleClose}>
      <Text isMarginless type='h3' isBold className='text-left'>
        Fundraising Settings
      </Text>
    </DialogHeader>
  )

  return (
    <Dialog isOpen={isOpen} size='large' isOverflowVisible isOpaque={false} toastContainerId='fundraising-settings'>
      <form className='w-full' noValidate onSubmit={handleSubmit}>
        <Container
          containerWidth='full'
          staticContent={staticContent}
          isScrollable
          isScrollShadowVisible
          isTopBarVisible={false}
          adjustHeight={200}
        >
          <Columns>
            <Column className='text-left'>
              <Text type='h4'>Donation Support Contact</Text>
              <Text isSecondaryColour isMarginless>
                Share the best contact email, and phone number for your supporters to reach you, and Givecloud will
                populate the information in your FAQ panel on your digital experiences.
              </Text>
            </Column>
            <Column columnWidth='four'>
              <Columns isResponsive={false}>
                <Column>
                  <EmailInput
                    label='Contact Email'
                    name='orgSupportEmail'
                    value={fundraisingValue.orgSupportEmail}
                    onChange={handleChange}
                  />
                </Column>
                <Column>
                  <PhoneInput
                    label='Contact Phone Number'
                    name='orgSupportNumber'
                    phoneNumber={fundraisingValue.orgSupportNumber}
                    country={fundraisingValue.orgSupportNumberCountryCode}
                    onChange={handlePhoneChange}
                  />
                </Column>
              </Columns>
            </Column>
          </Columns>
          <Columns>
            <Column className='text-left'>
              <Text type='h4'>Other Ways to Donate</Text>
              <Text isSecondaryColour>
                Specify up to 6 other ways your donors can contribute to your organization, and Givecloud will populate
                the information in your FAQ panel on your digital experiences.
              </Text>
            </Column>
            <Column columnWidth='four'>
              <DonationMethods />
            </Column>
          </Columns>
          <Columns>
            <Column className='text-left'>
              <Text type='h4'>Alternate FAQ</Text>
              <Text isSecondaryColour isMarginless>
                We've already built an extensive FAQ page that covers all the most commonly asked questions by your
                donors. If we've missed a question that's specific to your organization, this is your chance to add it.
              </Text>
            </Column>
            <Column columnWidth='four'>
              <Input
                aria-label='question alternate f a q'
                label='Question'
                name='orgFaqAlternativeQuestion'
                value={fundraisingValue.orgFaqAlternativeQuestion}
                onChange={handleChange}
              />
              <TextArea
                aria-label='answer alternate f a q'
                label='Answer'
                name='orgFaqAlternativeAnswer'
                value={fundraisingValue.orgFaqAlternativeAnswer}
                onChange={handleChange}
              />
            </Column>
          </Columns>
          <Columns>
            <Column className='text-left'>
              <Text type='h4'>Mailing Address for Checks</Text>
              <Text isSecondaryColour isMarginless>
                Add your mailing address to help direct your supporters to the correct information they need to send
                checks. Givecloud will populate the information in your FAQ panel on your digital experiences.
              </Text>
            </Column>
            <Column columnWidth='four'>
              <TextArea
                label='Mailing Address for Checks'
                name='orgCheckMailingAddress'
                value={fundraisingValue.orgCheckMailingAddress}
                onChange={handleChange}
              />
            </Column>
          </Columns>
          <Columns>
            <Column className='text-left'>
              <Text type='h4'>Privacy Link and Contact</Text>
              <Text isSecondaryColour isMarginless>
                Share a direct link to your privacy policy and the designated contact for your privacy officer, and
                Givecloud will populate the information in your Privacy & Legal panel on your digital experiences.
              </Text>
            </Column>
            <Column columnWidth='four'>
              <Columns isResponsive={false}>
                <Column columnWidth='four'>
                  <Input
                    addOn='https://'
                    label='Privacy Link'
                    name='orgPrivacyPolicyUrl'
                    value={fundraisingValue.orgPrivacyPolicyUrl}
                    onChange={handleChange}
                  />
                </Column>
                <Column>
                  <EmailInput
                    label='Privacy Contact'
                    name='orgPrivacyOfficerEmail'
                    value={fundraisingValue.orgPrivacyOfficerEmail}
                    onChange={handleChange}
                  />
                </Column>
              </Columns>
            </Column>
          </Columns>
        </Container>
        <Columns className='justify-end mt-4'>
          <Column columnWidth='small'>
            <Button onClick={handleClose} isOutlined>
              Close
            </Button>
          </Column>
          <Column columnWidth='small'>
            <Button aria-label='Save fundraising settings' isLoading={isLoading} type='submit'>
              Save
            </Button>
          </Column>
        </Columns>
      </form>
    </Dialog>
  )
}

FundraisingSettingsDialog.propTypes = {
  isOpen: PropTypes.bool,
  onClose: PropTypes.func,
}

export { FundraisingSettingsDialog }
