import { useTailwindBreakpoints } from '@/shared/hooks'
import { Skeleton, Box, Column, Columns } from '@/aerosol'
import styles from './FundraisingForm.scss'

const SkeletonFundraisingForm = () => {
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
      <Columns isMarginless isResponsive={false}>
        {renderPreviewImg()}
        <Column className='justify-center'>
          <Skeleton width='medium' />
          <Skeleton isMarginless width='small' />
        </Column>
        <Columns className='w-full' isMarginless isResponsive={false} isStackingOnMobile={false}>
          <Column className='justify-center'>
            <Skeleton width='small' />
            <Skeleton isMarginless width='medium' />
          </Column>
          <Column columnWidth='two' className='justify-center'>
            <Skeleton width='small' />
            <Skeleton isMarginless width='medium' />
          </Column>
          <Column columnWidth='two' className='items-end justify-center'>
            <Skeleton isMarginless isFullyRounded height='small' width='small' />
          </Column>
        </Columns>
      </Columns>
    </Box>
  )
}

export { SkeletonFundraisingForm }
