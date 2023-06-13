import { forwardRef } from 'react'
import PropTypes from 'prop-types'
import {
  Column,
  Columns,
  Toggle,
  SlideTransition,
  Alert,
  Accordion,
  AccordionContent,
  AccordionHeader,
  Text,
  RadioButton,
  RadioGroup,
  RadioTile,
  Input,
} from '@/aerosol'
import { faCircleInfo } from '@fortawesome/pro-regular-svg-icons'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './StickyReminderAccordion.scss'

import { useTailwindBreakpoints } from '@/shared/hooks'

const StickyReminderAccordion = forwardRef(({ isOpen, setIsOpen }, ref) => {
  const { medium, small } = useTailwindBreakpoints()
  const { remindersValue, setRemindersState, isOptionsReminderDescriptionError } = useFundraisingFormState()
  const { touchedInputs, errors } = remindersValue

  const handleFocus = ({ target: { name } }) => {
    setRemindersState({
      ...remindersValue,
      touchedInputs: {
        ...touchedInputs,
        [name]: '',
      },
    })
  }

  const handleBlur = ({ target: { name } }) => {
    setRemindersState({
      ...remindersValue,
      touchedInputs: {
        ...touchedInputs,
        [name]: name,
      },
    })
  }

  const handleChange = ({ target: { name, value } }) => {
    if (!value) {
      setRemindersState({
        ...remindersValue,
        [name]: '',
        errors: {
          ...errors,
          [name]: ['Field is required'],
        },
      })
    } else {
      setRemindersState({
        ...remindersValue,
        [name]: value,
        errors: {
          ...errors,
          [name]: [],
        },
      })
    }
  }

  const handlePositionChange = (embedOptionsReminderPosition) =>
    setRemindersState({ ...remindersValue, embedOptionsReminderPosition })

  const toggleEnabledState = () => {
    const isReminderDescriptionValid = !!remindersValue.embedOptionsReminderDescription.length
    const isExitConfirmationValid = !!remindersValue.exitConfirmationDescription.length
    if (remindersValue.embedOptionsReminderEnabled) {
      setRemindersState({
        ...remindersValue,
        touchedInputs: {
          ...touchedInputs,
          embedOptionsReminderDescription: isReminderDescriptionValid ? '' : 'isReminderDescriptionValid',
        },
        errors: {
          ...errors,
          embedOptionsReminderDescription: [],
        },
        embedOptionsReminderEnabled: false,
      })
    } else if (isReminderDescriptionValid) {
      setRemindersState({
        ...remindersValue,
        embedOptionsReminderEnabled: true,
      })
    } else {
      setRemindersState({
        ...remindersValue,
        embedOptionsReminderEnabled: true,
        touchedInputs: {
          exitConfirmationDescription: isExitConfirmationValid ? '' : 'exitConfirmationDescription',
          embedOptionsReminderDescription: isReminderDescriptionValid ? '' : 'embedOptionsReminderDescription',
        },
        errors: {
          exitConfirmationDescription: isExitConfirmationValid ? [] : ['Field is required'],
          embedOptionsReminderDescription: isReminderDescriptionValid ? [] : ['Field is required'],
        },
      })
    }
  }
  const getErrors = (name) => (!!touchedInputs?.[name] ? errors?.[name] : [])

  return (
    <Accordion hasBorderTop isOpen={isOpen} setIsOpen={setIsOpen} className={styles.root}>
      <AccordionHeader>
        <div className={styles.header}>
          <Text isError={isOptionsReminderDescriptionError} isBold isMarginless type='h5'>
            Sticky Reminder
          </Text>
          <Toggle
            isEnabled={remindersValue.embedOptionsReminderEnabled}
            setIsEnabled={toggleEnabledState}
            name='sticky reminder'
          />
        </div>
      </AccordionHeader>
      <AccordionContent>
        <Text className='mb-6' isSecondaryColour>
          Continue encouraging your supporters even after they've closed the donation experience.
        </Text>
        <SlideTransition isOpen={isOpen}>
          <Alert icon={faCircleInfo} iconPosition='center' type='info'>
            <Columns isMarginless>
              <Column columnWidth='six'>
                <Text isMarginless>This is only supported in the embedded donation experience.</Text>
              </Column>
            </Columns>
          </Alert>
        </SlideTransition>
        <div className='mt-10'>
          <Input
            ref={ref}
            charCountMax={50}
            value={remindersValue.embedOptionsReminderDescription}
            onChange={handleChange}
            name='embedOptionsReminderDescription'
            label='Sticky Reminder Heading'
            isDisabled={!remindersValue.embedOptionsReminderEnabled}
            onFocus={handleFocus}
            onBlur={handleBlur}
            errors={getErrors('embedOptionsReminderDescription')}
          />
        </div>
        <RadioGroup
          name='value'
          label='Position'
          showInput={false}
          checkedValue={remindersValue.embedOptionsReminderPosition}
          onChange={handlePositionChange}
          isDisabled={!remindersValue.embedOptionsReminderEnabled}
        >
          <Columns
            isWrapping={medium.greaterThan || small.lessThan}
            isResponsive={medium.lessThan}
            isStackingOnMobile={small.lessThan}
            isMarginless
          >
            <Column
              className='pl-0 sm:pt-0'
              isPaddingless={small.lessThan}
              columnWidth={small.lessThan ? 'three' : 'small'}
            >
              <RadioButton id='reminder-position-left' value='bottom_left'>
                <RadioTile>
                  <Text isBold isMarginless>
                    Left
                  </Text>
                </RadioTile>
              </RadioButton>
            </Column>
            <Column className='sm:pt-0' isPaddingless={small.lessThan} columnWidth={small.lessThan ? 'three' : 'small'}>
              <RadioButton id='reminder-position-center' value='bottom_center'>
                <RadioTile>
                  <Text isBold isMarginless>
                    Center
                  </Text>
                </RadioTile>
              </RadioButton>
            </Column>
            <Column className='sm:pt-0' isPaddingless={small.lessThan} columnWidth={small.lessThan ? 'three' : 'small'}>
              <RadioButton id='reminder-position-right' value='bottom_right'>
                <RadioTile>
                  <Text isBold isMarginless>
                    Right
                  </Text>
                </RadioTile>
              </RadioButton>
            </Column>
          </Columns>
        </RadioGroup>
      </AccordionContent>
    </Accordion>
  )
})

StickyReminderAccordion.displayName = 'StickyReminderAccordion'

StickyReminderAccordion.propTypes = {
  isOpen: PropTypes.bool,
  setIsOpen: PropTypes.func,
}

export { StickyReminderAccordion }
