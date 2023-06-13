import { useState } from 'react'
import { Box, Column, Columns, Text, Badge, TextArea } from '@/aerosol'
import { ThankYouPreview } from '@/screens/Fundraising/LivePreview/ThankYouPreview'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { useTailwindBreakpoints, useFocus } from '@/shared/hooks'
import styles from './ThankYouCard.scss'

const ThankYouCard = () => {
  const { medium } = useTailwindBreakpoints()
  const { thankYouValue, setThankYouState } = useFundraisingFormState()
  const [isMessageFocused, setIsMessagedFocused] = useState(false)
  const [isPreviewHovered, setIsPreviewHovered] = useState(false)
  const [messageRef, setMessageRef] = useFocus()

  const handleChange = (e) => {
    const { name, value } = e.target
    setThankYouState({ [name]: value })
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
        <ThankYouPreview
          isHovered={isPreviewHovered}
          isMessageFocused={isMessageFocused}
          messageOnClick={setMessageRef}
        />
        <Badge theme='secondary' className={styles.badge}>
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
          <Text className='mb-4' isMarginless isBold type='h5'>
            Thank You
          </Text>
          <Text isMarginless isSecondaryColour>
            Add an encouragement on the thank you screen.
          </Text>
          <TextArea
            isAutoGrowing
            isOptional
            ref={messageRef}
            charCountMax={100}
            value={thankYouValue.thankYouOnscreenMessage}
            onChange={handleChange}
            name='thankYouOnscreenMessage'
            label='On-Screen Message'
            onFocus={() => setIsMessagedFocused(true)}
            onBlur={() => setIsMessagedFocused(false)}
          />
        </Column>
      </Columns>
    </Box>
  )
}

export { ThankYouCard }
