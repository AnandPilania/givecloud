import { useTailwindBreakpoints } from '@/shared/hooks'
import { Skeleton, Box, Column, Columns } from '@/aerosol'

const SkeletonBrandingPanel = () => {
  const { large, medium } = useTailwindBreakpoints()

  const renderSaveButton = (isWithinViewPort) =>
    isWithinViewPort ? (
      <Column className='items-end' columnWidth='one'>
        <Skeleton width={large.lessThan ? 'full' : 'medium'} height='medium' />
      </Column>
    ) : null

  const renderRoundedSkeleton = () =>
    [...new Array(7)].map((_, i) => (
      <Column isPaddingless className='mr-4' key={i} columnWidth='small'>
        <Skeleton isFullyRounded width={large.lessThan ? 'medium' : 'small'} />
      </Column>
    ))

  return (
    <Box>
      <Columns>
        <Column>
          <Column>
            <Skeleton width='small' height='small' />
            <Skeleton width='medium' height='small' />
            <Skeleton width='large' height='small' />
          </Column>
        </Column>
        <Column columnWidth='four'>
          <Columns>
            <Column columnWidth='one'>
              <Skeleton width='small' height='small' />
              <Skeleton width='large' height='large' />
            </Column>
            {renderSaveButton(medium.greaterThan)}
          </Columns>
          <Columns className='ml-2' isWrapping isStackingOnMobile={false} isResponsive={false}>
            {renderRoundedSkeleton()}
          </Columns>
        </Column>
        {renderSaveButton(large.lessThan)}
      </Columns>
    </Box>
  )
}

export { SkeletonBrandingPanel }
