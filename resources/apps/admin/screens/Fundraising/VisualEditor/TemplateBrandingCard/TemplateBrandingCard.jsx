import { useState } from 'react'
import { Badge, Box, Column, Columns } from '@/aerosol'
import { LogoColourAccordion } from './LogoColourAccordion'
import { NameTemplateAccordion } from './NameTemplateAccordion'
import { DonationPreview } from '@/screens/Fundraising/LivePreview/DonationPreview'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './TemplateBrandingCard.scss'

const TemplateBrandingCard = () => {
  const { medium } = useTailwindBreakpoints()
  const [openAccordionId, setOpenAccordionId] = useState(1)

  const isAccordionOpen = (id) => id === openAccordionId

  const renderPreviewImage = () => {
    if (medium.lessThan) return null
    return (
      <Column columnWidth='four' className={styles.background}>
        <DonationPreview isSocialProofVisible={false} />
        <Badge theme='secondary' className={styles.badge}>
          sample
        </Badge>
      </Column>
    )
  }

  return (
    <Box isOverflowVisible isReducedPadding={medium.lessThan} className={styles.root} isMarginless>
      <Columns isMarginless className='h-full'>
        {renderPreviewImage()}
        <Column className='pt-0'>
          <NameTemplateAccordion isOpen={isAccordionOpen(1)} setIsOpen={() => setOpenAccordionId(1)} />
          <LogoColourAccordion isOpen={isAccordionOpen(2)} setIsOpen={() => setOpenAccordionId(2)} />
        </Column>
      </Columns>
    </Box>
  )
}

export { TemplateBrandingCard }
