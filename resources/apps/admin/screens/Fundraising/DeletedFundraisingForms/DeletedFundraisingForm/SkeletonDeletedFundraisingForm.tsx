import type { FC } from 'react'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { Skeleton, Box, Column, Columns } from '@/aerosol'
import styles from './DeletedFundraisingForm.styles.scss'

const SkeletonDeletedFundraisingForm: FC = () => {
  const { medium } = useTailwindBreakpoints()

  const renderPreviewImg = () =>
    medium.greaterThan ? (
      <Column columnWidth='two'>
        <div className={styles.imageContainer}>
          <Skeleton isMarginless height='full' width='full' />
        </div>
      </Column>
    ) : null

  return (
    <Box isReducedPadding>
      <Columns isMarginless className='items-center'>
        {renderPreviewImg()}
        <Column>
          <Skeleton width='medium' />
          <Skeleton isMarginless width='small' />
        </Column>
        <Columns className='w-full' isMarginless isResponsive={false} isStackingOnMobile={false}>
          <Column columnWidth='four' className='justify-center'>
            <Skeleton width='medium' />
            <Skeleton isMarginless width='small' />
          </Column>
          <Column columnWidth='six' className='justify-center'>
            <Skeleton width='medium' />
            <Skeleton isMarginless width='small' />
          </Column>
        </Columns>
        <Column columnWidth='one'>
          <Skeleton height='medium' width='full' />
        </Column>
      </Columns>
    </Box>
  )
}

export { SkeletonDeletedFundraisingForm }
