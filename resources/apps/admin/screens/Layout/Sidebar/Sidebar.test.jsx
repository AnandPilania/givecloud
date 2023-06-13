import { RecoilRoot } from 'recoil'
import { render, screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Sidebar } from '@/screens/Layout/Sidebar'
import { SidebarFirstLevelMenu } from '@/screens/Layout/Sidebar/SidebarFirstLevelMenu'
import { SidebarAlerts } from '@/screens/Layout/Sidebar/SidebarAlerts'
import { SidebarPinnedItems } from '@/screens/Layout/Sidebar/SidebarPinnedItems'
import SupporterSearch from '@/screens/Layout/Sidebar/SupporterSearch'
import { MockComponent } from '@/utilities/MockComponent'

jest.mock('@/screens/Layout/Sidebar/SidebarFirstLevelMenu', () => ({
  SidebarFirstLevelMenu: jest.fn((props) => MockComponent(props)),
}))
jest.mock('@/screens/Layout/Sidebar/SidebarAlerts', () => ({
  SidebarAlerts: jest.fn((props) => MockComponent(props)),
}))
jest.mock('@/screens/Layout/Sidebar/SidebarPinnedItems', () => ({
  SidebarPinnedItems: jest.fn((props) => MockComponent(props)),
}))

jest.mock('@/screens/Layout/Sidebar/SupporterSearch', () => jest.fn(() => null))

afterEach(() => {
  jest.clearAllMocks()
})

test('renders a Givecloud logo that calls toggleMenu when clicked', () => {
  const toggleDrawer = jest.fn()

  render(
    <RecoilRoot>
      <Sidebar toggleDrawer={toggleDrawer} />
    </RecoilRoot>
  )

  userEvent.click(screen.getByTestId('logoContainer'))

  expect(toggleDrawer).toHaveBeenCalled()
})

test('renders a close button when isMobile', () => {
  render(
    <RecoilRoot>
      <Sidebar isMobile />
    </RecoilRoot>
  )

  expect(screen.getByLabelText('Close button')).toBeInTheDocument()
})

test('does not render a close button when not isMobile', () => {
  render(
    <RecoilRoot>
      <Sidebar isMobile={false} />
    </RecoilRoot>
  )

  expect(screen.queryByLabelText('Close button')).toBeNull()
})

test('does not render SupporterSearch when behind feature flag', () => {
  render(
    <RecoilRoot>
      <Sidebar />
    </RecoilRoot>
  )

  expect(SupporterSearch).not.toHaveBeenCalled()
})

test('renders SidebarFirstLevelMenu', () => {
  render(
    <RecoilRoot>
      <Sidebar />
    </RecoilRoot>
  )

  expect(SidebarFirstLevelMenu).toHaveBeenCalled()
})

test('renders SidebarAlerts', () => {
  render(
    <RecoilRoot>
      <Sidebar />
    </RecoilRoot>
  )

  expect(SidebarAlerts).toHaveBeenCalled()
})

test('renders SidebarPinnedItems', () => {
  render(
    <RecoilRoot>
      <Sidebar />
    </RecoilRoot>
  )

  expect(SidebarPinnedItems).toHaveBeenCalled()
})

test('renders a link to give feedback', () => {
  render(
    <RecoilRoot>
      <Sidebar />
    </RecoilRoot>
  )

  const link = screen.getByText('Have Feedback? Let us know').closest('a')

  expect(link).toBeInTheDocument()
  expect(link).toHaveAttribute('href', '/jpanel/feedback')
  expect(within(link).getByLabelText('external-link')).toBeInTheDocument()
})
