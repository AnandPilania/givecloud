import { useState } from 'react'
import { Box, Column, Columns, TextArea, Text, Badge } from '@/aerosol'
import { EmailPreview } from '@/screens/Fundraising/LivePreview/EmailPreview'
import { useTailwindBreakpoints, useFocus } from '@/shared/hooks'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './EmailCard.scss'

const EmailCard = () => {
  const { medium } = useTailwindBreakpoints()
  const { emailValue, setEmailState } = useFundraisingFormState()
  const [isPreviewHovered, setIsPreviewHovered] = useState(false)
  const [messageRef, setMessageRef] = useFocus()
  const { touchedInputs, errors } = emailValue

  const handleChange = (e) => {
    const { name, value } = e.target
    if (!value) {
      setEmailState({
        ...emailValue,
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
      setEmailState({
        ...emailValue,
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

    setEmailState({
      ...emailValue,
      touchedInputs: {
        ...touchedInputs,
        [name]: name,
      },
    })
  }

  const handleFocus = ({ target }) => {
    const { name } = target
    setEmailState({
      ...emailValue,
      touchedInputs: {
        [name]: '',
      },
    })
  }

  const renderPreviewImage = () => {
    if (medium.lessThan) return null
    return (
      <Column
        columnWidth='four'
        className={styles.background}
        onMouseEnter={() => setIsPreviewHovered(true)}
        onMouseLeave={() => setIsPreviewHovered(false)}
      >
        <EmailPreview
          messageOnClick={setMessageRef}
          isOverlayVisible={!touchedInputs?.['thankYouEmailMessage'] || isPreviewHovered}
        />
        <Badge theme='secondary' className={styles.badge}>
          Sample
        </Badge>
      </Column>
    )
  }

  const getErrors = () => (!!touchedInputs?.['thankYouEmailMessage'] ? errors?.thankYouEmailMessage : [])

  return (
    <Box isReducedPadding={medium.lessThan} isMarginless className={styles.root}>
      <Columns isMarginless className='h-full'>
        {renderPreviewImage()}
        <Column>
          <Text isBold type='h5'>
            Email
          </Text>
          <TextArea
            isAutoGrowing
            ref={messageRef}
            label='Thank You Message'
            name='thankYouEmailMessage'
            value={emailValue.thankYouEmailMessage}
            onChange={handleChange}
            className='mb-8'
            onFocus={handleFocus}
            onBlur={handleBlur}
            errors={getErrors()}
            charCountMax={250}
          />
        </Column>
      </Columns>
    </Box>
  )
}

export { EmailCard }
