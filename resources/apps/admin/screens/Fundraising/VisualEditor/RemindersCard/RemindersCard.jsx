import { useState } from 'react'
import { Box, Column, Columns, Badge } from '@/aerosol'
import { RemindersPreview } from '@/screens/Fundraising/LivePreview/RemindersPreview'
import { useTailwindBreakpoints, useFocus } from '@/shared/hooks'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { ExitConfirmationAccordion } from './ExitConfirmationAccordion'
import { StickyReminderAccordion } from './StickyReminderAccordion'
import styles from './RemindersCard.scss'

const RemindersCard = () => {
  const [openAccordionId, setOpenAccordionId] = useState(1)
  const { medium } = useTailwindBreakpoints()
  const { remindersValue } = useFundraisingFormState()
  const [isPreviewHovered, setIsPreviewHovered] = useState(false)
  const [exitConfirmationRef, setExitConfirmationRef] = useFocus()
  const [stickyReminderRef, setStickyReminderRef] = useFocus()

  const handleAccordionFocus = (id) => {
    return (callback) => {
      setOpenAccordionId(id)
      return callback()
    }
  }

  const isAccordionOpen = (id) => id === openAccordionId

  const renderPreviewImage = () => {
    if (medium.lessThan) return null
    return (
      <Column
        columnWidth='four'
        className={styles.background}
        onMouseEnter={() => setIsPreviewHovered(true)}
        onMouseLeave={() => setIsPreviewHovered(false)}
      >
        <RemindersPreview
          isHovered={isPreviewHovered}
          isExitConfirmationFocused={isAccordionOpen(1)}
          exitConfirmationOnClick={() => handleAccordionFocus(1)(setExitConfirmationRef)}
          isStickyReminderFocused={isAccordionOpen(2) && remindersValue.embedOptionsReminderEnabled}
          stickyReminderOnClick={() => handleAccordionFocus(2)(setStickyReminderRef)}
          isStickyReminderEnabled={remindersValue.embedOptionsReminderEnabled}
          stickyReminderPosition={remindersValue.embedOptionsReminderPosition}
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
        <Column className='pt-0'>
          <ExitConfirmationAccordion
            isOpen={isAccordionOpen(1)}
            ref={exitConfirmationRef}
            setIsOpen={() => setOpenAccordionId(1)}
          />
          <StickyReminderAccordion
            isOpen={isAccordionOpen(2)}
            ref={stickyReminderRef}
            setIsOpen={() => setOpenAccordionId(2)}
          />
        </Column>
      </Columns>
    </Box>
  )
}

export { RemindersCard }
