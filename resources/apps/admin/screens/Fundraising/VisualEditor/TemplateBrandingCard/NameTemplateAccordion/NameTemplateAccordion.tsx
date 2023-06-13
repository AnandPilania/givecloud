import type { FocusEvent, ChangeEvent, FC } from 'react'
import type { AccordionProps } from '@/aerosol'
import type { Template } from '@/screens/Fundraising/VisualEditor/TemplatesCard/templates'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faInfoCircle, faArrowRight } from '@fortawesome/pro-regular-svg-icons'
import {
  Accordion,
  AccordionContent,
  AccordionHeader,
  CarouselButton,
  Column,
  Columns,
  Dropdown,
  DropdownButton,
  DropdownContent,
  DropdownItem,
  DropdownItems,
  DropdownLabel,
  Label,
  Input,
  Text,
  Tooltip,
} from '@/aerosol'
import { StandardTemplateSVG } from '@/screens/Fundraising/VisualEditor/TemplatesCard/svgs/StandardTemplateSVG'
import { TilesTemplateSVG } from '@/screens/Fundraising/VisualEditor/TemplatesCard/svgs/TilesTemplateSVG'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { templates } from '@/screens/Fundraising/VisualEditor/TemplatesCard/templates'

import styles from './NameTemplateAccordion.styles.scss'

const mappedTemplates = {
  standard: <StandardTemplateSVG className={styles.image} />,
  amount_tiles: <TilesTemplateSVG className={styles.image} />,
}

const NameTemplateAccordion: FC<AccordionProps> = ({ isOpen, setIsOpen }) => {
  const { large, medium } = useTailwindBreakpoints()
  const { templateValue, setTemplateState, isNameError } = useFundraisingFormState()
  const { errors } = templateValue

  const handleChange = ({ target }: ChangeEvent<HTMLInputElement>) => {
    const { name, value } = target
    if (!value) {
      setTemplateState({
        ...templateValue,
        [name]: '',
        errors: {
          [name]: ['Field is required'],
        },
        touchedInputs: {
          [name]: '',
        },
      })
    } else {
      setTemplateState({
        ...templateValue,
        [name]: value,
        errors: {
          [name]: [],
        },
      })
    }
  }

  const handleBlur = ({ target }: FocusEvent<HTMLInputElement>) => {
    const { name } = target

    setTemplateState({
      ...templateValue,
      touchedInputs: {
        ...templateValue?.touchedInputs,
        [name]: name,
      },
    })
  }

  const handleFocus = ({ target }: FocusEvent<HTMLInputElement>) => {
    const { name } = target

    setTemplateState({
      ...templateValue,
      touchedInputs: {
        ...templateValue?.touchedInputs,
        [name]: '',
      },
    })
  }

  const getErrors = () => (isNameError ? errors?.name : [])

  const renderTemplateImage = () => {
    if (large.lessThan) return null
    return (
      <Column className={styles.imageContainer} columnWidth={medium.greaterThan ? 'three' : 'small'}>
        {mappedTemplates[templateValue.template.type]}
      </Column>
    )
  }

  const handleClick = (template: Template) => {
    setTemplateState({
      ...templateValue,
      template,
    })
  }

  const nameTooltipContent = (
    <>
      <Text isBold>Your donors won’t see this</Text>
      <Text isMarginless type='footnote'>
        This is just for you and your team to stay organized.
      </Text>
    </>
  )

  const renderTemplatesCTA = () => {
    const renderDropdownItem = (template: Template) =>
      template.isAvailable ? (
        <DropdownItem value={template.title} onClick={() => handleClick(template)} key={template.title}>
          {template.title}
        </DropdownItem>
      ) : null

    if (medium.lessThan) {
      return (
        <Dropdown value={templateValue.template?.title} aria-label='Select template'>
          <DropdownLabel>
            <Text isMarginless>Select Template</Text>
          </DropdownLabel>
          <DropdownContent>
            <DropdownButton>{templateValue.template?.title}</DropdownButton>
            <DropdownItems>{templates.map((template) => renderDropdownItem(template))}</DropdownItems>
          </DropdownContent>
        </Dropdown>
      )
    }
    return (
      <CarouselButton isClean className={styles.carouselButton} indexToNavigate={1}>
        View templates
        <FontAwesomeIcon className='ml-2' icon={faArrowRight} />
      </CarouselButton>
    )
  }

  return (
    <Accordion isOpen={isOpen} setIsOpen={setIsOpen} className={styles.root}>
      <AccordionHeader>
        <Text isError={isNameError} isBold isMarginless type='h5'>
          Name & Template
        </Text>
      </AccordionHeader>
      <AccordionContent>
        <Text className='mb-6' isSecondaryColour>
          Customize this fundraising experience to match your organization's unique personality.
        </Text>
        <Label isError={isNameError} htmlFor='name'>
          <span className='mr-2'>Experience Name</span>
          <Tooltip placement='right' theme={isNameError ? 'error' : 'info'} tooltipContent={nameTooltipContent}>
            <FontAwesomeIcon icon={faInfoCircle} className={isNameError ? styles.iconError : styles.icon} />
          </Tooltip>
        </Label>
        <Input
          name='name'
          value={templateValue.name}
          onChange={handleChange}
          onBlur={handleBlur}
          onFocus={handleFocus}
          errors={getErrors()}
          charCountMax={50}
          isLabelHidden
        />
        <Columns isMarginless isResponsive={false} isStackingOnMobile={false} className={styles.columns}>
          {renderTemplateImage()}
          <Column className={styles.templateContainer} columnWidth='six'>
            <Text isBold>{templateValue.template?.title} Experience</Text>
            <Text isMarginless className='mb-4' isSecondaryColour>
              {templateValue.template?.subtitle}
            </Text>
            {renderTemplatesCTA()}
          </Column>
        </Columns>
      </AccordionContent>
    </Accordion>
  )
}

export { NameTemplateAccordion }
