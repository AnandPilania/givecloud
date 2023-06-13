import { useTailwindBreakpoints } from '@/shared/hooks'
import { Skeleton, Box, Column, Columns } from '@/aerosol'

const SkeletonFundraisingPanel = () => {
  const { large, medium } = useTailwindBreakpoints()

  const renderEditButton = (isWithinViewport) =>
    isWithinViewport ? (
      <Column className='items-end' columnWidth='one'>
        <Skeleton width={large.lessThan ? 'full' : 'medium'} height='medium' />
      </Column>
    ) : null

  return (
    <Box>
      <Columns>
        <Column>
          <Skeleton width='medium' height='small' />
        </Column>
        <Column columnWidth='four'>
          <Columns>
            <Column>
              <Skeleton width='small' height='small' />
              <Skeleton width='medium' height='small' />
              <Skeleton width='small' height='small' />
            </Column>
            {renderEditButton(medium.greaterThan)}
          </Columns>
          <Columns isResponsive={false}>
            <Column columnWidth='one'>
              <Skeleton width='small' height='small' />
              <Skeleton width='medium' height='small' />
            </Column>
          </Columns>
          <Columns isResponsive={false}>
            <Column columnWidth='one'>
              <Skeleton width='small' height='small' />
              <Skeleton width='medium' height='small' />
            </Column>
          </Columns>
          <Columns isResponsive={false}>
            <Column columnWidth='one'>
              <Skeleton width='small' height='small' />
              <Skeleton width='medium' height='small' />
            </Column>
            <Column columnWidth='one'>
              <Skeleton width='small' height='small' />
              <Skeleton width='medium' height='small' />
            </Column>
          </Columns>
        </Column>
        {renderEditButton(large.lessThan)}
      </Columns>
    </Box>
  )
}

export { SkeletonFundraisingPanel }
