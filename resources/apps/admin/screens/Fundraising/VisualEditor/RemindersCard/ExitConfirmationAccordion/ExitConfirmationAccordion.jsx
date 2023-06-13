import { forwardRef } from 'react'
import PropTypes from 'prop-types'
import { Accordion, AccordionContent, AccordionHeader, Text, TextArea } from '@/aerosol'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'

const ExitConfirmationAccordion = forwardRef(({ isOpen, setIsOpen }, ref) => {
  const { remindersValue, setRemindersState, isExitConfirmationDescriptionError } = useFundraisingFormState()
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

  const getErrors = (name) => (!!touchedInputs?.[name] ? errors?.[name] : [])

  return (
    <Accordion isOpen={isOpen} setIsOpen={setIsOpen}>
      <AccordionHeader>
        <Text isError={isExitConfirmationDescriptionError} isBold isMarginless type='h5'>
          Exit Confirmation
        </Text>
      </AccordionHeader>
      <AccordionContent>
        <Text className='mb-6' isSecondaryColour>
          Encourage donors to complete their donation before exiting the experience.
        </Text>
        <TextArea
          isAutoGrowing
          ref={ref}
          charCountMax={60}
          value={remindersValue.exitConfirmationDescription}
          onChange={handleChange}
          name='exitConfirmationDescription'
          label='Exit Confirmation Description'
          className='mb-6'
          onFocus={handleFocus}
          onBlur={handleBlur}
          errors={getErrors('exitConfirmationDescription')}
        />
      </AccordionContent>
    </Accordion>
  )
})

ExitConfirmationAccordion.displayName = 'ExitConfirmationAccordion'

ExitConfirmationAccordion.propTypes = {
  isOpen: PropTypes.bool,
  setIsOpen: PropTypes.func,
}

export { ExitConfirmationAccordion }
