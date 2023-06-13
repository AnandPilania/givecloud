import { Skeleton, Column, Columns, Container } from '@/aerosol'
import { SkeletonFundraisingForm } from './FundraisingForm'
import { useTailwindBreakpoints } from '@/shared/hooks'

const SkeletonFundraisingForms = () => {
  const { medium } = useTailwindBreakpoints()

  const staticContent = (
    <Columns isMarginless isResponsive={false}>
      <Column>
        <Skeleton width='large' height='medium' />
      </Column>
      <Column columnWidth='one' className='items-end'>
        <Skeleton width={medium.lessThan ? 'full' : 'medium'} height='medium' />
      </Column>
    </Columns>
  )

  return (
    <Container data-testid='loading' isScrollable staticContent={staticContent}>
      <SkeletonFundraisingForm />
      <SkeletonFundraisingForm />
      <SkeletonFundraisingForm />
      <SkeletonFundraisingForm />
      <SkeletonFundraisingForm />
      <SkeletonFundraisingForm />
    </Container>
  )
}

export { SkeletonFundraisingForms }
