import { Box, Button, Column, Columns, Text, Badge } from '@/aerosol'
import { Link } from '@/components/Link'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './OrgPanel.scss'
import { OrgSettingsDialog } from './OrgSettingsDialog'
import { useHistory, useLocation } from 'react-router-dom'
import { useOrgSettingsState } from './useOrgSettingsState'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faExclamationCircle } from '@fortawesome/pro-regular-svg-icons'

const OrgPanel = () => {
  const { large, medium } = useTailwindBreakpoints()
  const history = useHistory()
  const { search, pathname } = useLocation()
  const isOpen = search.includes('organizationSettings')
  const { orgValue } = useOrgSettingsState()

  const onClose = () => history.goBack()

  const isOrgCharityNumberValid = !!orgValue.orgLegalNumber

  const renderEditButton = (isWithinViewport) =>
    isWithinViewport ? (
      <Column columnWidth='small'>
        <Button
          aria-label={!isOrgCharityNumberValid ? 'add charity number' : 'edit organization settings'}
          to={{ pathname, search: 'organizationSettings' }}
          isOutlined
          size='small'
          theme={isOrgCharityNumberValid ? 'primary' : 'error'}
        >
          {isOrgCharityNumberValid ? 'Edit' : 'Add charity number'}
        </Button>
      </Column>
    ) : null

  const renderWebsite = () =>
    !orgValue.orgWebsite ? (
      <Text isMarginless isSecondaryColour>
        No Organization Site
      </Text>
    ) : (
      <Link href={orgValue.orgWebsite} className={styles.link}>
        {orgValue.orgWebsite}
      </Link>
    )

  const renderLocale = () => (!orgValue.locale ? 'No language selected' : orgValue.locale)
  const renderAddress = () => (!orgValue.orgLegalAddress ? 'No address provided' : orgValue.orgLegalAddress)
  const renderMarketCategory = () => (!orgValue.marketCategory ? 'No market category' : orgValue.marketCategory)
  const renderFundraisingGoal = () =>
    !orgValue.annualFundraisingGoal ? 'No fundraising goal' : orgValue.annualFundraisingGoal

  const renderBadge = () =>
    isOrgCharityNumberValid ? null : (
      <Badge theme='error' className='mb-2'>
        <Text isMarginless type='footnote' className='uppercase'>
          required
        </Text>
      </Badge>
    )

  const renderCharityNumber = () =>
    !isOrgCharityNumberValid ? (
      <Text isMarginless className='text-red-600'>
        <FontAwesomeIcon icon={faExclamationCircle} className='text-red-600 mr-2' />
        Charity number missing
      </Text>
    ) : (
      <Text isMarginless isSecondaryColour>
        {orgValue.orgLegalNumber}
      </Text>
    )

  return (
    <>
      <Box>
        <Columns>
          <Column>
            <div className={styles.badgeContainer}>
              <Text isBold type='h4' className={styles.text}>
                Organization
              </Text>
              {renderBadge()}
            </div>
            <Text isSecondaryColour isMarginless>
              Tell us more about your organization, and we will ensure your digital experiences and automated emails
              reflect your brand and values.
            </Text>
          </Column>
          <Column columnWidth='four'>
            <Columns isResponsive={false}>
              <Column>
                <Text type='h5' isBold>
                  {orgValue.orgLegalName}
                </Text>
                <Text className={styles.address}>{renderAddress()}</Text>
                <Text isMarginless>Charity Number</Text>
                {renderCharityNumber()}
              </Column>
              {renderEditButton(medium.greaterThan)}
            </Columns>
            <Columns isResponsive={false}>
              <Column columnWidth='one'>
                <Text isMarginless>Market Category</Text>
                <Text isMarginless isSecondaryColour>
                  {renderMarketCategory()}
                </Text>
              </Column>
              <Column columnWidth='one'>
                <Text isMarginless>Website</Text>
                {renderWebsite()}
              </Column>
            </Columns>
            <Columns isResponsive={false}>
              <Column columnWidth='one'>
                <Text isMarginless>Annual Fundraising Goal</Text>
                <Text isMarginless isSecondaryColour>
                  {renderFundraisingGoal()}
                </Text>
              </Column>
              <Column columnWidth='one'>
                <Text isMarginless>Localization</Text>
                <Text isMarginless isSecondaryColour>
                  {renderLocale()}
                </Text>
              </Column>
            </Columns>
          </Column>
          {renderEditButton(large.lessThan)}
        </Columns>
      </Box>
      <OrgSettingsDialog isOpen={isOpen} onClose={onClose} />
    </>
  )
}

export { OrgPanel }
