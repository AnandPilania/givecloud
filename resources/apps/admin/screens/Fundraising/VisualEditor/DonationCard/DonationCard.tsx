import type { FC } from 'react'
import { useState } from 'react'
import classnames from 'classnames'
import { useTailwindBreakpoints, useFocus } from '@/shared/hooks'
import { Box, Column, Columns, Badge } from '@/aerosol'
import { TransparencyPromiseAccordion } from './TransparencyPromiseAccordion'
import { TransparencyStatementAccordion } from './TransparencyStatementAccordion'
import { SocialProofAccordion } from './SocialProofAccordion'
import { DonationPreview } from '@/screens/Fundraising/LivePreview/DonationPreview'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { TilesDefaultAmountAccordion } from './TilesDefaultAmountAccordion'
import { StandardDefaultAmountAccordion } from './StandardDefaultAmountAccordion'
import { TodayAndMonthlyAccordion } from './TodayAndMonthlyAccordion'
import styles from './DonationCard.styles.scss'

const DonationCard: FC = () => {
  const { medium } = useTailwindBreakpoints()
  const { transparencyPromiseValue, socialProofValue, templateValue } = useFundraisingFormState()
  const [openAccordionId, setOpenAccordionId] = useState(1)
  const [isPreviewHovered, setIsPreviewHovered] = useState(false)
  const [socialProofRef, setSocialProofRef] = useFocus<HTMLInputElement>()
  const [todayAndMonthlyRef, setTodayAndMonthlyRef] = useFocus<HTMLInputElement>()
  const [defaultAmountRef, setDefaultAmountRef] = useFocus<HTMLInputElement>()
  const [transparencyPromiseRef, setTransparencyPromiseRef] = useFocus<HTMLInputElement>()

  const isAccordionOpen = (id: number) => id === openAccordionId

  const handleAccordionFocus = (id: number) => {
    return (callback: () => void) => {
      setOpenAccordionId(id)
      return callback()
    }
  }

  const renderPreview = () => {
    if (medium.lessThan) return null
    return (
      <Column
        columnWidth='four'
        className={styles.background}
        onMouseEnter={() => setIsPreviewHovered(true)}
        onMouseLeave={() => setIsPreviewHovered(false)}
      >
        <DonationPreview
          isHovered={isPreviewHovered}
          socialProofOnClick={() => handleAccordionFocus(1)(setSocialProofRef)}
          isSocialProofFocused={isAccordionOpen(1) && socialProofValue.socialProofEnabled}
          todayAndMonthlyOnClick={() => handleAccordionFocus(2)(setTodayAndMonthlyRef)}
          isTodayAndMonthlyFocused={isAccordionOpen(2)}
          defaultAmountOnClick={() => handleAccordionFocus(3)(setDefaultAmountRef)}
          isDefaultAmountFocused={isAccordionOpen(3)}
          transparencyPromiseOnClick={() => handleAccordionFocus(4)(setTransparencyPromiseRef)}
          isTransparencyPromiseFocused={isAccordionOpen(4) && transparencyPromiseValue.transparencyPromiseEnabled}
        />
        <Badge theme='secondary' className={styles.badge}>
          Sample
        </Badge>
      </Column>
    )
  }

  const renderTransparencyAccordion = () => {
    const props = {
      ref: transparencyPromiseRef,
      isOpen: isAccordionOpen(4),
      setIsOpen: () => setOpenAccordionId(4),
    }

    return transparencyPromiseValue.transparencyPromiseType === 'statement' ? (
      <TransparencyStatementAccordion {...props} />
    ) : (
      <TransparencyPromiseAccordion {...props} />
    )
  }

  const renderDefaultAmountAccordion = () => {
    const props = {
      ref: defaultAmountRef,
      isOpen: isAccordionOpen(3),
      setIsOpen: () => setOpenAccordionId(3),
    }

    return templateValue.template?.title === 'Tiles' ? (
      <TilesDefaultAmountAccordion {...props} />
    ) : (
      <StandardDefaultAmountAccordion {...props} />
    )
  }

  return (
    <Box isReducedPadding={medium.lessThan} className={styles.root} isMarginless>
      <Columns isMarginless className='h-full'>
        {renderPreview()}
        <Column className='pt-0'>
          <SocialProofAccordion
            ref={socialProofRef}
            isOpen={isAccordionOpen(1)}
            setIsOpen={() => setOpenAccordionId(1)}
          />
          <div className={classnames(styles.column, templateValue.template?.title === 'Standard' && styles.reverse)}>
            <TodayAndMonthlyAccordion
              ref={todayAndMonthlyRef}
              isOpen={isAccordionOpen(2)}
              setIsOpen={() => setOpenAccordionId(2)}
            />
            {renderDefaultAmountAccordion()}
          </div>
          {renderTransparencyAccordion()}
        </Column>
      </Columns>
    </Box>
  )
}

export { DonationCard }
