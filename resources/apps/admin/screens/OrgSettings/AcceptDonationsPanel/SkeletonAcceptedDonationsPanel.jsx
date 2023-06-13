import { Skeleton, Box, Column, Columns } from '@/aerosol'

const SkeletonAcceptDonationsPanel = () => {
  return (
    <Box>
      <Columns>
        <Column>
          <Skeleton width='small' height='small' />
          <Skeleton width='medium' height='small' />
          <Skeleton width='large' height='small' />
        </Column>
        <Column columnWidth='four'>
          <Skeleton width='full' height='large' />
          <Skeleton width='full' height='large' />
          <Columns>
            <Column>
              <Skeleton width='full' height='medium' />
            </Column>
            <Column columnWidth='one'>
              <Skeleton width='full' height='medium' />
            </Column>
          </Columns>
          <Columns>
            <Column>
              <Skeleton width='full' height='medium' />
            </Column>
            <Column columnWidth='one'>
              <Skeleton width='full' height='medium' />
            </Column>
          </Columns>
        </Column>
      </Columns>
    </Box>
  )
}

export { SkeletonAcceptDonationsPanel }
