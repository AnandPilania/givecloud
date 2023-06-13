import { render, screen } from '@testing-library/react'
import { RecoilRoot } from 'recoil'
import { SidebarAlerts } from './SidebarAlerts'
import { PaymentMethodProblemChip } from './PaymentMethodProblemChip'
import { setConfig } from '@/utilities/config'
import { MockComponent } from '@/utilities/MockComponent'

jest.mock('@/screens/Layout/Sidebar/SidebarAlerts/PaymentMethodProblemChip', () => ({
  PaymentMethodProblemChip: jest.fn((props) => MockComponent(props)),
}))

afterEach(() => {
  jest.clearAllMocks()
})

test('renders trial days remaining chip if isTrial and trialDaysRemaining', () => {
  const trialDaysRemaining = 1
  setConfig({ isTrial: true, trialDaysRemaining })

  render(
    <RecoilRoot>
      <SidebarAlerts />
    </RecoilRoot>
  )

  expect(screen.getByText(`in ${trialDaysRemaining} Days`)).toBeInTheDocument()
})

test('does not render trial days remaining chip if not isTrial', () => {
  const trialDaysRemaining = 1
  setConfig({ isTrial: false, trialDaysRemaining })

  render(
    <RecoilRoot>
      <SidebarAlerts />
    </RecoilRoot>
  )

  expect(screen.queryByText(`in ${trialDaysRemaining} Days`)).toBeNull()
})

test('does not render trial days remaining chip if not trialDaysRemaining', () => {
  const trialDaysRemaining = 0
  setConfig({ isTrial: true, trialDaysRemaining })

  render(
    <RecoilRoot>
      <SidebarAlerts />
    </RecoilRoot>
  )

  expect(screen.queryByText(`in ${trialDaysRemaining} Days`)).toBeNull()
})

test('renders test mode chip if isTestMode', () => {
  setConfig({ isTestMode: true })

  render(
    <RecoilRoot>
      <SidebarAlerts />
    </RecoilRoot>
  )

  expect(screen.getByText('in Test Mode')).toBeInTheDocument()
})

test('does not render test mode chip if not isTestMode', () => {
  setConfig({ isTestMode: false })

  render(
    <RecoilRoot>
      <SidebarAlerts />
    </RecoilRoot>
  )

  expect(screen.queryByText('in Test Mode')).toBeNull()
})

test('renders PaymentMethodProblemChip if not isTrial, isMissingPaymentMethod and not isDevelopment', () => {
  setConfig({ isTrial: false, isMissingPaymentMethod: true, isDevelopment: false })

  render(
    <RecoilRoot>
      <SidebarAlerts />
    </RecoilRoot>
  )

  expect(PaymentMethodProblemChip).toHaveBeenCalled()
})

test('does not render PaymentMethodProblemChip if isTrial', () => {
  setConfig({ isTrial: true, isMissingPaymentMethod: true, isDevelopment: false })

  render(
    <RecoilRoot>
      <SidebarAlerts />
    </RecoilRoot>
  )

  expect(PaymentMethodProblemChip).not.toHaveBeenCalled()
})

test('does not render PaymentMethodProblemChip if not isMissingPaymentMethod', () => {
  setConfig({ isTrial: false, isMissingPaymentMethod: false, isDevelopment: false })

  render(
    <RecoilRoot>
      <SidebarAlerts />
    </RecoilRoot>
  )

  expect(PaymentMethodProblemChip).not.toHaveBeenCalled()
})

test('does not render PaymentMethodProblemChip if isDevelopment', () => {
  setConfig({ isTrial: false, isMissingPaymentMethod: true, isDevelopment: true })

  render(
    <RecoilRoot>
      <SidebarAlerts />
    </RecoilRoot>
  )

  expect(PaymentMethodProblemChip).not.toHaveBeenCalled()
})
