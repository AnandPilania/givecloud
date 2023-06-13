import type { FC } from 'react'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { Skeleton, Column, Columns } from '@/aerosol'
import styles from './FundraisingFormDashboardHeader.styles.scss'

const SkeletonFundraisingFormDashboardHeader: FC = () => {
  const { large } = useTailwindBreakpoints()

  const renderViewButton = () => {
    if (large.lessThan) return null
    return <Skeleton width='small' height='medium' />
  }
  return (
    <Columns isMarginless isResponsive={false} isStackingOnMobile={false} className={styles.root}>
      <Column>
        <Skeleton width='large' height='medium' />
      </Column>
      <Column className={styles.buttonContainer}>
        {renderViewButton()}
        <Skeleton width='small' height='medium' className='ml-2' />
      </Column>
    </Columns>
  )
}

export { SkeletonFundraisingFormDashboardHeader }
