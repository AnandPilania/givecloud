import { Skeleton, Column, Columns, Container, Box } from '@/aerosol'
import { SkeletonFundraisingFormDashboardHeader } from './FundraisingFormDashboardHeader'

const SkeletonFundraisingFormDashboard = () => {
  return (
    <Container containerWidth='extraSmall' data-testid='loading'>
      <SkeletonFundraisingFormDashboardHeader />
      <Columns isMarginless>
        <Column>
          <Box isFullHeight>
            <Columns isResponsive={false} isStackingOnMobile={false}>
              <Column>
                <Skeleton height='medium' width='large' />
              </Column>
              <Column columnWidth='small'>
                <Skeleton isFullyRounded />
              </Column>
            </Columns>
            <Columns className='h-full'>
              <Column columnWidth='six' className=' h-[250px] lg:h-[90%]'>
                <Skeleton height='full' width='full' className='mb-4' />
              </Column>
            </Columns>
          </Box>
        </Column>
        <Column>
          <Box>
            <Columns isResponsive={false} isStackingOnMobile={false} isMarginless className='h-full'>
              <Column>
                <Skeleton height='medium' width='large' />
              </Column>
              <Column columnWidth='small' className='self-center h-full justify-center'>
                <Skeleton isMarginless isFullyRounded />
              </Column>
            </Columns>
          </Box>
          <Box>
            <Columns isResponsive={false} isStackingOnMobile={false} isMarginless className='h-full'>
              <Column>
                <Skeleton height='medium' width='large' />
              </Column>
              <Column columnWidth='small' className='self-center h-full justify-center'>
                <Skeleton isMarginless isFullyRounded />
              </Column>
            </Columns>
          </Box>
          <Box>
            <Columns isResponsive={false} isStackingOnMobile={false} isMarginless className='h-full'>
              <Column>
                <Skeleton height='medium' width='large' />
              </Column>
              <Column columnWidth='small' className='self-center h-full justify-center'>
                <Skeleton isMarginless isFullyRounded />
              </Column>
            </Columns>
          </Box>
          <Box>
            <Columns isResponsive={false} isStackingOnMobile={false} isMarginless className='h-full'>
              <Column>
                <Skeleton height='medium' width='large' />
              </Column>
              <Column columnWidth='small' className='self-center h-full justify-center'>
                <Skeleton isMarginless isFullyRounded />
              </Column>
            </Columns>
          </Box>
        </Column>
      </Columns>
      <Columns isMarginless>
        <Column columnWidth='four'>
          <Box>
            <Columns isResponsive={false} isStackingOnMobile={false} isMarginless className='h-full'>
              <Column>
                <Skeleton height='medium' width='large' />
                <Skeleton height='small' width='medium' />
              </Column>
              <Column className='h-full'>
                <Skeleton height='large' width='full' />
              </Column>
            </Columns>
          </Box>
        </Column>
        <Column columnWidth='four'>
          <Box>
            <Columns isResponsive={false} isStackingOnMobile={false} isMarginless className='h-full'>
              <Column>
                <Skeleton height='medium' width='large' />
                <Skeleton height='small' width='medium' />
              </Column>
              <Column className='h-full self-center'>
                <Skeleton height='large' width='full' />
              </Column>
            </Columns>
          </Box>
        </Column>
      </Columns>
      <Columns isMarginless>
        <Column isPaddingless columnWidth='four' className='md:flex-row'>
          <Column>
            <Box>
              <Columns isResponsive={false} isStackingOnMobile={false} isMarginless className='h-full'>
                <Column>
                  <Skeleton height='medium' width='large' />
                  <Skeleton height='small' width='medium' />
                  <Skeleton height='small' width='small' />
                </Column>
              </Columns>
            </Box>
          </Column>
          <Column>
            <Box>
              <Columns isResponsive={false} isStackingOnMobile={false} isMarginless className='h-full'>
                <Column>
                  <Skeleton height='medium' width='large' />
                  <Skeleton height='small' width='medium' />
                  <Skeleton height='small' width='small' />
                </Column>
              </Columns>
            </Box>
          </Column>
        </Column>
        <Column columnWidth='four' />
      </Columns>
    </Container>
  )
}

export { SkeletonFundraisingFormDashboard }
