import type { FC } from 'react'
import type { ColoursType } from '@/shared/constants/theme'
import type { AccordionProps, ImageData, RemoveImageData } from '@/aerosol'
import { useState } from 'react'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faInfoCircle, faChevronRight } from '@fortawesome/pro-regular-svg-icons'
import {
  useColourErrors,
  ColourPicker,
  Accordion,
  AccordionContent,
  AccordionHeader,
  ImagePicker,
  Label,
  Text,
  Tooltip,
} from '@/aerosol'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './LogoColourAccordion.styles.scss'

type Props = AccordionProps

const LogoColourAccordion: FC<Props> = ({ isOpen, setIsOpen }) => {
  const { large } = useTailwindBreakpoints()
  const { brandingValue, setBrandingState, isColourError } = useFundraisingFormState()
  const { getColourErrors } = useColourErrors()
  const [areOptionsVisible, setAreOptionsVisible] = useState(false)

  const handleColourChange = (brandingColour: ColoursType) => {
    if (brandingColour.value === 'custom' && getColourErrors(brandingColour.code).length) {
      setBrandingState({
        ...brandingValue,
        errors: {
          colour: getColourErrors(brandingColour.code),
        },
      })
    } else {
      setBrandingState({
        ...brandingValue,
        brandingColour,
        errors: {
          colour: [],
        },
      })
    }
  }

  const handleLogoChange = ({ id, url, name }: ImageData) => {
    if (name) {
      setBrandingState({
        ...brandingValue,
        [name]: {
          id,
          full: url,
        },
      })
    }
  }

  const handleRemoveLogo = ({ name }: RemoveImageData) => {
    if (name) {
      setBrandingState({
        ...brandingValue,
        [name]: {
          id: undefined,
          full: undefined,
        },
      })
    }
  }

  const logoTooltipContent = (
    <Text isMarginless>For best results, crop your logo to be around 800x350 with a transparent background.</Text>
  )

  return (
    <Accordion hasBorderTop isOpen={isOpen} setIsOpen={setIsOpen}>
      <AccordionHeader>
        <Text isError={isColourError} isBold isMarginless type='h5'>
          Logo & Color
        </Text>
      </AccordionHeader>
      <AccordionContent>
        <Label htmlFor='upload-logo'>
          <span className='mr-2'>Logo</span>
          <Tooltip tooltipContent={logoTooltipContent}>
            <FontAwesomeIcon icon={faInfoCircle} className='text-blue-600' />
          </Tooltip>
        </Label>
        <ImagePicker
          name='brandingLogo'
          objectFit='contain'
          id='upload-brand-logo'
          image={brandingValue?.brandingLogo.full}
          removeImage={handleRemoveLogo}
          onChange={handleLogoChange}
        />
        <Label isError={isColourError}>Theme Colour</Label>
        <ColourPicker
          aria-label='update theme colour'
          placement={large.lessThan ? 'top' : 'bottom'}
          colour={brandingValue.brandingColour}
          onChange={handleColourChange}
          errors={brandingValue.errors?.colour}
        />
        <Accordion isOpen={areOptionsVisible} setIsOpen={() => setAreOptionsVisible(!areOptionsVisible)}>
          <AccordionHeader isIconVisible={false} className={styles.accordionHeader}>
            <Text isMarginless>
              Additional Settings
              <FontAwesomeIcon
                icon={faChevronRight}
                className={classNames(styles.optionsIcon, areOptionsVisible && styles.open)}
                aria-hidden='true'
              />
            </Text>
          </AccordionHeader>
          <AccordionContent>
            <Label htmlFor='upload-monthly-giving-logo'>
              <span className='mr-2'>Monthly Giving Logo</span>
              <Tooltip tooltipContent={logoTooltipContent}>
                <FontAwesomeIcon icon={faInfoCircle} className='text-blue-600' />
              </Tooltip>
            </Label>
            <ImagePicker
              name='brandingMonthlyLogo'
              objectFit='contain'
              id='upload-monthly-giving-logo'
              image={brandingValue?.brandingMonthlyLogo.full}
              removeImage={handleRemoveLogo}
              onChange={handleLogoChange}
            />
          </AccordionContent>
        </Accordion>
      </AccordionContent>
    </Accordion>
  )
}

export { LogoColourAccordion }
