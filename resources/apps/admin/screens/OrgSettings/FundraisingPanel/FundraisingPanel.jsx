import { useHistory, useLocation } from 'react-router-dom'
import { Box, Button, Column, Columns, Text } from '@/aerosol'
import { FundraisingSettingsDialog } from './FundraisingSettingsDialog'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { useFundraisingSettingsState } from '@/screens/OrgSettings/FundraisingPanel/useFundraisingSettingsState'
import styles from './FundraisingPanel.scss'

const FundraisingPanel = () => {
  const { fundraisingValue } = useFundraisingSettingsState()
  const { large, medium } = useTailwindBreakpoints()
  const history = useHistory()
  const { search, pathname } = useLocation()
  const isOpen = search.includes('fundraisingSettings')
  const onClose = () => history.goBack()

  const renderEditButton = (isWithinViewport) =>
    isWithinViewport ? (
      <Column columnWidth='small'>
        <Button
          aria-label='edit fundraising settings'
          isOutlined
          size='small'
          to={{ pathname, search: 'fundraisingSettings' }}
        >
          Edit
        </Button>
      </Column>
    ) : null

  const renderOtherWaysToDonate = () => {
    const nonEmptyWaysToDonate = fundraisingValue.orgOtherWaysToDonate?.filter(({ label, href }) => !!label && !!href)
    const isOtherWayToDonateValid =
      !!fundraisingValue.orgOtherWaysToDonate[0]?.label || !!fundraisingValue.orgOtherWaysToDonate[0]?.href

    return isOtherWayToDonateValid ? (
      nonEmptyWaysToDonate.map(({ id, href, label }) => (
        <div className={styles.textWrapper} key={id}>
          <Text isTruncated isMarginless className='mr-2'>
            {label}
          </Text>
          <Text isMarginless isSecondaryColour isTruncated type='footnote' className='max-w-[50%]'>
            {href}
          </Text>
        </div>
      ))
    ) : (
      <Text isMarginless isSecondaryColour>
        No Other Ways to Donate
      </Text>
    )
  }

  const renderSupportEmail = () =>
    fundraisingValue.orgSupportEmail ? fundraisingValue.orgSupportEmail : 'No Contact Email'
  const renderSupportPhoneNumber = () =>
    fundraisingValue.orgSupportNumber ? fundraisingValue.orgSupportNumber : 'No Contact Phone Number'
  const renderFaqAlternativeQuestion = () =>
    fundraisingValue.orgFaqAlternativeQuestion ? fundraisingValue.orgFaqAlternativeQuestion : 'No Alternative Question'
  const renderFaqAlternativeAnswer = () =>
    fundraisingValue.orgFaqAlternativeAnswer ? fundraisingValue.orgFaqAlternativeAnswer : 'No Alternative Answer'
  const renderChecksMailingAddress = () =>
    fundraisingValue.orgCheckMailingAddress ? fundraisingValue.orgCheckMailingAddress : 'No Mailing Address for Checks'
  const renderPrivacyPolicyUrl = () =>
    fundraisingValue.orgPrivacyPolicyUrl ? fundraisingValue.orgPrivacyPolicyUrl : 'No Privacy Link'
  const renderPrivacyOfficerEmail = () =>
    fundraisingValue.orgPrivacyOfficerEmail ? fundraisingValue.orgPrivacyOfficerEmail : 'No Privacy Contact'

  return (
    <>
      <Box>
        <Columns>
          <Column columnWidth='two'>
            <Text type='h4' isBold>
              Fundraising
            </Text>
            <Text isSecondaryColour isMarginless>
              Tell us more about how your organization fundraises. We'll ensure your Givecloud digital experiences
              include the information your supporters need to give.
            </Text>
          </Column>
          <Column>
            <Columns>
              <Column className={styles.padding}>
                <Columns>
                  <Column columnWidth='two'>
                    <Text>Donation Support Contact</Text>
                    <Text isMarginless>Email</Text>
                    <Text isMarginless isSecondaryColour isTruncated>
                      {renderSupportEmail()}
                    </Text>
                  </Column>
                  <Column columnWidth='one' className='justify-end'>
                    <Text isMarginless>Phone Number</Text>
                    <Text isMarginless isSecondaryColour isTruncated>
                      {renderSupportPhoneNumber()}
                    </Text>
                  </Column>
                </Columns>
              </Column>
              {renderEditButton(medium.greaterThan)}
            </Columns>
            <Columns>
              <Column>
                <Text>Other Ways to Donate</Text>
                {renderOtherWaysToDonate()}
              </Column>
            </Columns>
            <Columns>
              <Column>
                <Text>Alternate FAQ</Text>
                <Text isMarginless isTruncated className='max-w-[75%]'>
                  {renderFaqAlternativeQuestion()}
                </Text>
                <Text isMarginless isSecondaryColour isTruncated className='max-w-[75%]'>
                  {renderFaqAlternativeAnswer()}
                </Text>
              </Column>
            </Columns>
            <Columns>
              <Column>
                <Text isMarginless>Mailing Address for Checks</Text>
                <Text isMarginless isSecondaryColour isTruncated className='max-w-[75%]'>
                  {renderChecksMailingAddress()}
                </Text>
              </Column>
            </Columns>
            <Columns>
              <Column columnWidth='one'>
                <Text isMarginless>Privacy Link</Text>
                <Text isMarginless isSecondaryColour isTruncated>
                  {renderPrivacyPolicyUrl()}
                </Text>
              </Column>
              <Column columnWidth='one'>
                <Text isMarginless>Privacy Contact</Text>
                <Text isMarginless isSecondaryColour isTruncated>
                  {renderPrivacyOfficerEmail()}
                </Text>
              </Column>
            </Columns>
          </Column>
          {renderEditButton(large.lessThan)}
        </Columns>
      </Box>
      <FundraisingSettingsDialog isOpen={isOpen} onClose={onClose} />
    </>
  )
}

export { FundraisingPanel }
