import { Skeleton, Column, Columns, Container } from '@/aerosol'
import { SkeletonDeletedFundraisingForm } from './DeletedFundraisingForm'

const SkeletonDeletedFundraisingForms = () => (
  <Container>
    <Columns isMarginPreserved isResponsive={false} data-testid='loading'>
      <Column>
        <Skeleton width='medium' height='medium' />
      </Column>
    </Columns>
    <SkeletonDeletedFundraisingForm />
    <SkeletonDeletedFundraisingForm />
    <SkeletonDeletedFundraisingForm />
    <SkeletonDeletedFundraisingForm />
    <SkeletonDeletedFundraisingForm />
    <SkeletonDeletedFundraisingForm />
  </Container>
)

export { SkeletonDeletedFundraisingForms }
