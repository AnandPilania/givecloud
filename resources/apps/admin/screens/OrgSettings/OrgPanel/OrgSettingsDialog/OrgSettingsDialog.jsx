import { Fragment, useState, useEffect } from 'react'
import { useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCircleInfo } from '@fortawesome/pro-regular-svg-icons'
import {
  Button,
  Column,
  Columns,
  Container,
  Dialog,
  DialogHeader,
  Dropdown,
  DropdownContent,
  DropdownButton,
  DropdownDivider,
  DropdownHeader,
  DropdownItems,
  DropdownItem,
  DropdownLabel,
  Input,
  Label,
  Text,
  Tooltip,
  TextArea,
  triggerToast,
} from '@/aerosol'
import { TimeZoneInput } from './TimeZoneInput'
import { LegalCountryCommandInput } from './LegalCountryCommandInput'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { useOrgSettingsState } from '@/screens/OrgSettings/OrgPanel/useOrgSettingsState'
import { useUpdateOrgSettingsMutation } from './useUpdateOrgSettingsMutation'
import { markets, staff, languages, goals } from './constants'
import configState from '@/atoms/config'
import styles from './OrgSettingsDialog.scss'

const OrgSettingsDialog = ({ isOpen, onClose }) => {
  const { isSuperUser } = useRecoilValue(configState)
  const { orgValue, setOrgValue } = useOrgSettingsState()
  const [currentOrgState, setCurrentOrgState] = useState(orgValue)
  const { small, medium } = useTailwindBreakpoints()
  const { mutate, isLoading, isSuccess, reset } = useUpdateOrgSettingsMutation()

  useEffect(() => {
    reset()
  }, [isOpen])

  const handleClose = () => {
    if (!isSuccess) setOrgValue(currentOrgState)
    onClose()
  }

  const onSuccess = ({ data: { data: response } }) => {
    triggerToast({
      type: 'success',
      header: 'Organization settings updated!',
      options: { containerId: 'org-settings' },
    })
    setCurrentOrgState(response)
  }

  const handleSubmit = (e) => {
    e.preventDefault()
    if (isLoading) return null

    mutate(
      {
        ...orgValue,
      },
      {
        onSuccess,
        OnError: () =>
          triggerToast({
            type: 'error',
            header: `Sorry there was an error`,
            options: { containerId: 'org-settings' },
          }),
      }
    )
  }

  const handleChange = ({ name, value }) => {
    setOrgValue({
      ...orgValue,
      [name]: value,
    })
  }

  const handleInputChange = ({ target: { name, value } }) => handleChange({ name, value })

  const staticContent = (
    <DialogHeader onClose={handleClose}>
      <Text isBold className='text-left' type='h3'>
        Organization Settings
      </Text>
    </DialogHeader>
  )

  const renderMarketDropdownItems = () => {
    const renderDropdownItems = (items) =>
      items.map((item) => (
        <DropdownItem
          key={item}
          onClick={({ target }) => handleChange({ name: 'marketCategory', value: target.value })}
          value={item}
        />
      ))

    const renderDropdownDivider = (index) => (index > 0 ? <DropdownDivider isMarginless key={index} /> : null)

    const renderDropdownSegment = (market, index) => (
      <Fragment key={market[0]}>
        {renderDropdownDivider(index)}
        <DropdownHeader className='text-center'>
          <Text isMarginless isBold>
            {market[0]}
          </Text>
        </DropdownHeader>
        {renderDropdownItems(market[1])}
      </Fragment>
    )

    return Object.entries(markets).map((market, index) => renderDropdownSegment(market, index))
  }

  const renderLanguageDropdownItems = () =>
    Object.keys(languages).map((value) => (
      <DropdownItem
        key={value}
        value={value}
        onClick={({ target }) => handleChange({ name: 'locale', value: target.value })}
      >
        {languages[value]}
      </DropdownItem>
    ))

  const renderGoalDropdownItems = () =>
    goals.map((goal) => (
      <DropdownItem
        key={goal}
        value={goal}
        onClick={({ target }) => handleChange({ name: 'annualFundraisingGoal', value: target.value })}
      />
    ))

  const renderStaffDropdownItems = () =>
    staff.map((member) => (
      <DropdownItem
        key={member}
        value={member}
        onClick={({ target }) => handleChange({ name: 'numberOfEmployees', value: target.value })}
      />
    ))

  const tooltipContent = (
    <Text isMarginless isBold>
      Contact Support to Change
    </Text>
  )

  const charityTooltipContent = (
    <Text isMarginless isBold>
      Without the charity number, you won't be able to start fundraising.
    </Text>
  )

  const getCharityNumberError = () => (!orgValue.orgLegalNumber ? ['Charity number missing'] : [])
  const isCharityError = !!getCharityNumberError().length

  const handleCommandInputChange = (value) => setOrgValue({ ...orgValue, orgLegalCountry: value })

  const renderLegalCountryInput = () =>
    isSuperUser ? (
      <LegalCountryCommandInput selected={orgValue.orgLegalCountry} setSelected={handleCommandInputChange} />
    ) : (
      <Input name='orgLegalCountry' isReadOnly value={orgValue.orgLegalCountry} />
    )

  return (
    <Dialog size='large' isOpen={isOpen} isOpaque={false} isOverflowVisible toastContainerId='org-settings'>
      <form data-testid='org-settings-form' noValidate className={styles.form} onSubmit={handleSubmit}>
        <Container
          containerWidth='full'
          staticContent={staticContent}
          isScrollable
          isScrollShadowVisible
          isTopBarVisible={false}
          adjustHeight={small.greaterThan ? 200 : 180}
        >
          <Columns>
            <Column className='text-left'>
              <Text type='h4'>Legal Name & Address</Text>
              <Text isSecondaryColour>
                Share your legal name, address and website with Givecloud to populate the information in your privacy
                and legal panel on your digital experiences.
              </Text>
            </Column>
            <Column columnWidth='four'>
              <Input
                name='orgLegalName'
                label='Legal Name'
                value={orgValue.orgLegalName}
                onChange={handleInputChange}
              />
              <TextArea
                name='orgLegalAddress'
                label='Legal Address'
                value={orgValue.orgLegalAddress}
                onChange={handleInputChange}
              />
              <Input
                addOn='https://'
                onChange={handleInputChange}
                label='Website'
                name='orgWebsite'
                value={orgValue.orgWebsite}
              />
            </Column>
          </Columns>
          <Columns>
            <Column className='text-left'>
              <Text type='h4'>Charitable Registration</Text>
              <Text isSecondaryColour>
                Add your charity number and Givecloud will populate the information in your private and legal panel on
                your digital experiences.
              </Text>
            </Column>
            <Column columnWidth='four'>
              <Columns isResponsive={false}>
                <Column>
                  <Label htmlFor='orgLegalCountry'>
                    <span className='mr-2'> Registered Country</span>
                    <Tooltip tooltipContent={tooltipContent}>
                      <FontAwesomeIcon icon={faCircleInfo} className='text-blue-600' />
                    </Tooltip>
                  </Label>
                  {renderLegalCountryInput()}
                </Column>
                <Column>
                  <Label isError={isCharityError} htmlFor='orgLegalNumber'>
                    <span className='mr-2'>Charity Number</span>
                    <Tooltip theme={isCharityError ? 'error' : 'info'} tooltipContent={charityTooltipContent}>
                      <FontAwesomeIcon
                        icon={faCircleInfo}
                        className={classNames(isCharityError ? 'text-red-600' : 'text-blue-600')}
                      />
                    </Tooltip>
                  </Label>
                  <Input
                    errors={getCharityNumberError()}
                    name='orgLegalNumber'
                    isMarginless
                    value={orgValue.orgLegalNumber}
                    onChange={handleInputChange}
                  />
                </Column>
              </Columns>
            </Column>
          </Columns>
          <Columns>
            <Column className='text-left'>
              <Text type='h4'>Localization</Text>
              <Text isSecondaryColour>
                Set your primary language, and Givecloud will use this language for your website, text-to-give, digital
                experiences and more. Add your local timezone so your Givecloud admin reflects your local time.
              </Text>
            </Column>
            <Column columnWidth='four'>
              <Columns isResponsive={false}>
                <Column>
                  <Dropdown aria-label='language' value={orgValue.locale} isFullWidth>
                    <DropdownLabel>
                      <Text isMarginless>Language</Text>
                    </DropdownLabel>
                    <DropdownContent>
                      <DropdownButton isOutlined>{languages[orgValue.locale] || orgValue.locale}</DropdownButton>
                      <DropdownItems>{renderLanguageDropdownItems()}</DropdownItems>
                    </DropdownContent>
                  </Dropdown>
                </Column>
                <Column>
                  <TimeZoneInput
                    selected={orgValue.timezone}
                    setSelected={(value) => handleChange({ name: 'timezone', value })}
                  />
                </Column>
              </Columns>
            </Column>
          </Columns>
          <Columns>
            <Column className='text-left'>
              <Text type='h4'>Get to Know You</Text>
              <Text isSecondaryColour>
                We'd love to get to know you! Please share your market category, organization size and annual
                fundraising goal. The information is not visible to the public and is only used to help Givecloud better
                understand how we can help you.
              </Text>
            </Column>
            <Column columnWidth='four'>
              <Columns isResponsive={false}>
                <Column>
                  <Dropdown aria-label='market category' value={orgValue.marketCategory} isFullWidth>
                    <DropdownLabel>
                      <Text isMarginless>Market Category</Text>
                    </DropdownLabel>
                    <DropdownContent>
                      <DropdownButton isOutlined>{orgValue.marketCategory}</DropdownButton>
                      <DropdownItems className='h-56 overflow-y-auto pt-0'>{renderMarketDropdownItems()}</DropdownItems>
                    </DropdownContent>
                  </Dropdown>
                  <Dropdown aria-label='annual fundraising goal' value={orgValue.annualFundraisingGoal} isFullWidth>
                    <DropdownLabel>
                      <Text isMarginless>Annual Fundraising Goal</Text>
                    </DropdownLabel>
                    <DropdownContent>
                      <DropdownButton isOutlined>{orgValue.annualFundraisingGoal}</DropdownButton>
                      <DropdownItems>{renderGoalDropdownItems()}</DropdownItems>
                    </DropdownContent>
                  </Dropdown>
                </Column>
                <Column>
                  <Dropdown aria-label='organization size' value={orgValue.numberOfEmployees} isFullWidth>
                    <DropdownLabel>
                      <Text isMarginless>Organization Size</Text>
                    </DropdownLabel>
                    <DropdownContent>
                      <DropdownButton isOutlined>{orgValue.numberOfEmployees}</DropdownButton>
                      <DropdownItems>{renderStaffDropdownItems()}</DropdownItems>
                    </DropdownContent>
                  </Dropdown>
                </Column>
              </Columns>
            </Column>
          </Columns>
        </Container>
        <Columns className={styles.buttonContainer}>
          <Column columnWidth={medium.greaterThan ? 'small' : 'three'}>
            <Button onClick={handleClose} isOutlined>
              Close
            </Button>
          </Column>
          <Column columnWidth={medium.greaterThan ? 'small' : 'three'} className={styles.paddingBottomless}>
            <Button aria-label='Save organization settings' isLoading={isLoading} type='submit'>
              Save
            </Button>
          </Column>
        </Columns>
      </form>
    </Dialog>
  )
}

OrgSettingsDialog.propTypes = {
  isOpen: PropTypes.bool,
  onClose: PropTypes.func,
}

export { OrgSettingsDialog }
