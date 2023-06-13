import type { FC } from 'react'
import { WidgetContent } from '@/components'
import { Column, Columns, Skeleton } from '@/aerosol'
import styles from './JoinTeamPeerToPeerWidgetContent.styles.scss'

const SkeletonJoinTeamPeerToPeerWidgetContent: FC = () => {
  return (
    <WidgetContent className={styles.root}>
      <Skeleton isFullyRounded width='large' height='large' />
      <Skeleton width='medium' height='medium' />
      <Columns className='w-full' isMarginless isResponsive={false} isStackingOnMobile={false}>
        <Column columnWidth='small'>
          <Skeleton isFullyRounded width='small' height='small' />
        </Column>
        <Column columnWidth='six'>
          <Skeleton width='full' height='medium' />
        </Column>
        <Column columnWidth='small'>
          <Skeleton isFullyRounded width='small' height='small' />
        </Column>
      </Columns>
      <Skeleton width='medium' height='medium' />
    </WidgetContent>
  )
}

export { SkeletonJoinTeamPeerToPeerWidgetContent }
