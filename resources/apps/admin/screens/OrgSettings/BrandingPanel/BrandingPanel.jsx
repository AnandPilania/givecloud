import { useState } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faInfoCircle } from '@fortawesome/pro-regular-svg-icons'
import {
  Box,
  Button,
  Column,
  Columns,
  ImagePicker,
  Label,
  ColourPicker,
  Text,
  Tooltip,
  triggerToast,
  useColourErrors,
} from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { useBrandingSettingsState } from './useBrandingSettingsState'
import { useUpdateBrandingSettingsMutation } from './useUpdateBrandingSettingsMutation'
import styles from './BrandingPanel.scss'

const BrandingPanel = () => {
  const { large, medium } = useTailwindBreakpoints()
  const { brandingValue, setBrandingValue } = useBrandingSettingsState()
  const { mutate, isLoading } = useUpdateBrandingSettingsMutation()
  const [isValueUpdated, setIsValueUpdated] = useState(false)
  const { getColourErrors } = useColourErrors()
  const isColourError = !!brandingValue.errors.length

  const handleColourChange = (orgPrimaryColor) => {
    if (orgPrimaryColor.value === 'custom' && getColourErrors(orgPrimaryColor.code).length) {
      setIsValueUpdated(false)
      setBrandingValue({
        ...brandingValue,
        errors: getColourErrors(orgPrimaryColor.code),
      })
    } else {
      setIsValueUpdated(true)
      setBrandingValue({
        ...brandingValue,
        orgPrimaryColor,
        errors: [],
      })
    }
  }

  const handleLogoChange = ({ id: orgLogoId, url: orgLogoUrl }) => {
    setIsValueUpdated(true)
    setBrandingValue({
      ...brandingValue,
      orgLogo: {
        orgLogoId,
        orgLogoUrl,
      },
    })
  }

  const handleRemoveLogo = () => {
    setIsValueUpdated(true)
    setBrandingValue({
      ...brandingValue,
      orgLogo: {
        orgLogoId: undefined,
        orgLogoUrl: undefined,
      },
    })
  }

  const onSuccess = () => {
    triggerToast({
      type: 'success',
      header: 'Branding settings updated!',
    })
    setIsValueUpdated(false)
  }

  const handleClick = () => {
    if (isLoading) return null

    mutate(
      {
        orgPrimaryColor: brandingValue.orgPrimaryColor.code,
        orgLogo: brandingValue?.orgLogo?.orgLogoId || brandingValue?.orgLogo?.id,
      },
      {
        onSuccess,
        OnError: () => {
          triggerToast({
            type: 'error',
            header: 'Sorry there was an error updating your settings.',
          })
        },
      }
    )
  }

  const tooltipContent = <Text isMarginless>Change the logo or theme colour to save</Text>

  const renderSaveButton = (isWithinViewPort) =>
    isWithinViewPort ? (
      <Column columnWidth='small'>
        <Tooltip tooltipContent={tooltipContent} isHidden={isValueUpdated}>
          <Button
            isFullWidth={large.lessThan}
            isDisabled={!isValueUpdated}
            onClick={handleClick}
            isLoading={isLoading}
            isOutlined
            size='small'
            aria-label='Save Branding Settings'
          >
            Save
          </Button>
        </Tooltip>
      </Column>
    ) : null

  const colorTooltipContent = (
    <Text isMarginless>Select the primary theme color of your Givecloud digital experiences. </Text>
  )

  const logoTooltipContent = <Text isMarginless>Add a Logo. We recommend using a PNG file, roughly 800x500.</Text>

  return (
    <Box isOverflowVisible className={styles.overflow}>
      <Columns>
        <Column>
          <Text isBold type='h4'>
            Branding
          </Text>
          <Text isSecondaryColour isMarginless>
            Customize your campaign with your organization’s unique brand.
          </Text>
        </Column>
        <Column columnWidth='four'>
          <Columns>
            <Column columnWidth='one'>
              <div className={styles.textContainer}>
                <Label className='mr-2 mb-0' id='brand-logo'>
                  Logo
                </Label>
                <Tooltip tooltipContent={logoTooltipContent}>
                  <FontAwesomeIcon className={styles.tooltipIcon} icon={faInfoCircle} />
                </Tooltip>
              </div>
              <ImagePicker
                objectFit='contain'
                id='brand-logo'
                onChange={handleLogoChange}
                removeImage={handleRemoveLogo}
                image={brandingValue?.orgLogo?.thumb || brandingValue?.orgLogo?.orgLogoUrl}
                isMarginless
              />
            </Column>
            {renderSaveButton(medium.greaterThan)}
          </Columns>
          <div className={styles.textContainer}>
            <Text isError={isColourError} isMarginless className='mr-2'>
              Theme color
            </Text>
            <Tooltip tooltipContent={colorTooltipContent}>
              <FontAwesomeIcon
                className={isColourError ? styles.tooltipIconError : styles.tooltipIcon}
                icon={faInfoCircle}
              />
            </Tooltip>
          </div>
          <ColourPicker
            aria-label='update branding colour'
            placement={large.lessThan ? 'top' : 'right'}
            colour={brandingValue?.orgPrimaryColor}
            onChange={handleColourChange}
            errors={brandingValue.errors}
          />
        </Column>
        {renderSaveButton(large.lessThan)}
      </Columns>
    </Box>
  )
}

export { BrandingPanel }
