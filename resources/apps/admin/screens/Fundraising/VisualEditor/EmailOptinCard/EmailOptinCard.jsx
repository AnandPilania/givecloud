import { useState } from 'react'
import { Badge, Box, Column, Columns, TextArea, Text, Toggle } from '@/aerosol'
import { EmailOptinPreview } from '@/screens/Fundraising/LivePreview/EmailOptinPreview'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { useTailwindBreakpoints, useFocus } from '@/shared/hooks'
import styles from './EmailOptinCard.scss'

const EmailOptinCard = () => {
  const { medium } = useTailwindBreakpoints()
  const { emailOptinValue, setEmailOptInState } = useFundraisingFormState()
  const [isPreviewHovered, setIsPreviewHovered] = useState(false)
  const [optInMessageRef, setOptInMessageRef] = useFocus()

  const { touchedInputs, errors } = emailOptinValue

  const handleChange = (e) => {
    const { name, value } = e.target
    if (!value) {
      setEmailOptInState({
        ...emailOptinValue,
        [name]: '',
        errors: {
          ...errors,
          [name]: ['Field is required'],
        },
        touchedInputs: {
          [name]: '',
        },
      })
    } else {
      setEmailOptInState({
        ...emailOptinValue,
        [name]: value,
        errors: {
          ...errors,
          [name]: [],
        },
      })
    }
  }

  const handleBlur = ({ target }) => {
    const { name } = target

    setEmailOptInState({
      ...emailOptinValue,
      touchedInputs: {
        ...touchedInputs,
        [name]: name,
      },
    })
  }

  const handleFocus = ({ target }) => {
    const { name } = target
    setEmailOptInState({
      ...emailOptinValue,
      touchedInputs: {
        ...touchedInputs,
        [name]: '',
      },
    })
  }

  const handleToggleState = () => {
    const isEmailOptinDescriptionValid = !!emailOptinValue.emailOptinDescription.length

    if (emailOptinValue.emailOptinEnabled) {
      setEmailOptInState({
        ...emailOptinValue,
        emailOptinEnabled: false,
        touchedInputs: { emailOptinDescription: isEmailOptinDescriptionValid ? '' : 'emailOptinDescription' },
        errors: {
          ...errors,
          emailOptinDescription: [],
        },
      })
    } else {
      setEmailOptInState({
        ...emailOptinValue,
        emailOptinEnabled: true,
        touchedInputs: { emailOptinDescription: isEmailOptinDescriptionValid ? '' : 'emailOptinDescription' },
        errors: {
          ...errors,
          emailOptinDescription: isEmailOptinDescriptionValid ? [] : ['Field is required'],
        },
      })
    }
  }

  const getErrors = (name) => (!!touchedInputs?.[name] ? errors?.[name] : [])

  const renderPreviewImage = () => {
    if (medium.lessThan) return null
    return (
      <Column
        columnWidth='four'
        className={styles.background}
        onMouseEnter={() => setIsPreviewHovered(true)}
        onMouseLeave={() => setIsPreviewHovered(false)}
      >
        <EmailOptinPreview
          isHovered={isPreviewHovered}
          isMessageInputFocused={!touchedInputs?.['emailOptinDescription']}
          messageOnClick={setOptInMessageRef}
          isDisabled={!emailOptinValue.emailOptinEnabled}
        />
        <Badge className={styles.badge} theme='secondary'>
          Sample
        </Badge>
      </Column>
    )
  }

  return (
    <Box isReducedPadding={medium.lessThan} className={styles.root} isMarginless>
      <Columns isMarginless className='h-full'>
        {renderPreviewImage()}
        <Column>
          <div className={styles.header}>
            <Text isMarginless isBold type='h5'>
              Email Opt-In
            </Text>
            <Toggle
              name='email opt-in'
              isEnabled={emailOptinValue.emailOptinEnabled}
              setIsEnabled={handleToggleState}
            />
          </div>
          <Text isMarginless isSecondaryColour>
            Encourage supporters to opt-in for communications focused on the impact that they've invested in.
          </Text>
          <TextArea
            isAutoGrowing
            ref={optInMessageRef}
            charCountMax={100}
            value={emailOptinValue.emailOptinDescription}
            onChange={handleChange}
            name='emailOptinDescription'
            label='Opt-In Message'
            onFocus={handleFocus}
            onBlur={handleBlur}
            errors={getErrors('emailOptinDescription')}
            isDisabled={!emailOptinValue.emailOptinEnabled}
          />
        </Column>
      </Columns>
    </Box>
  )
}

export { EmailOptinCard }
