import { Suspense } from 'react'
import { useRecoilValue } from 'recoil'
import { Column, Columns, Container, Text } from '@/aerosol'
import { Skeleton } from '@/aerosol'
import { AcceptDonationsPanel, SkeletonAcceptDonationsPanel } from './AcceptDonationsPanel'
import { BrandingPanel, SkeletonBrandingPanel } from './BrandingPanel'
import { FundraisingPanel, SkeletonFundraisingPanel } from './FundraisingPanel'
import { GivecloudPlanPanel } from './GivecloudPlanPanel'
import { IntegrationsPanel } from './IntegrationsPanel'
import { OrgPanel, SkeletonOrgPanel } from './OrgPanel'
import { SecurityPanel } from './SecurityPanel'
import { SupporterTrackingPanel } from './SupporterTrackingPanel'
import { TeammatesPanel } from './TeammatesPanel'
import { OrgSettingsAlert } from './OrgSettingsAlert'
import usePageTitle from '@/hooks/usePageTitle'
import { useTailwindBreakpoints } from '@/shared/hooks'
import configState from '@/atoms/config'

const OrgSettings = () => {
  const { isGivecloudExpress } = useRecoilValue(configState)
  const { small } = useTailwindBreakpoints()

  usePageTitle('Organization Settings')

  return (
    <Container containerWidth='medium'>
      <Columns>
        <Column isPaddingless={small.lessThan}>
          <Text isBold type='h1' className='mb-6 mt-2'>
            Organization Settings
          </Text>
          <Suspense fallback={<Skeleton width='full' height='large' />}>
            <OrgSettingsAlert />
          </Suspense>
          <Suspense fallback={<SkeletonOrgPanel />}>
            <OrgPanel />
          </Suspense>
          <Suspense fallback={<SkeletonAcceptDonationsPanel />}>
            <AcceptDonationsPanel />
          </Suspense>
          <Suspense fallback={<SkeletonBrandingPanel />}>
            <BrandingPanel />
          </Suspense>
          <Suspense fallback={<SkeletonFundraisingPanel />}>
            <FundraisingPanel />
          </Suspense>
          <Columns>
            <Column>
              <TeammatesPanel />
            </Column>
            <Column>
              <GivecloudPlanPanel hasUpgraded={!isGivecloudExpress} />
            </Column>
          </Columns>
          <Columns isResponsive={!isGivecloudExpress}>
            <Column>
              <SupporterTrackingPanel hasUpgraded={!isGivecloudExpress} />
            </Column>
            <Column>
              <SecurityPanel hasUpgraded={!isGivecloudExpress} />
            </Column>
          </Columns>
          <Columns isResponsive={!isGivecloudExpress}>
            <Column>
              <IntegrationsPanel hasUpgraded={!isGivecloudExpress} />
            </Column>
            <Column />
          </Columns>
        </Column>
      </Columns>
    </Container>
  )
}

export { OrgSettings }
