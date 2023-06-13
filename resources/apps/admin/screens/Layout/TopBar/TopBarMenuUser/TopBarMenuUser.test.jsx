import { RecoilRoot } from 'recoil'
import { render, screen } from '@testing-library/react'
import { TopBarMenuUser } from '@/screens/Layout/TopBar/TopBarMenuUser'
import { PROFILE_PATH, SETTINGS_GENERAL_PATH, LOGOUT_PATH } from '@/constants/pathConstants'
import { setConfig } from '@/utilities/config'

afterEach(() => {
  jest.clearAllMocks()
})

test('renders a list', () => {
  render(
    <RecoilRoot>
      <TopBarMenuUser />
    </RecoilRoot>
  )

  expect(screen.getByRole('list')).toBeInTheDocument()
})

test('renders a list item with the users full name, client name, and email address', () => {
  const contextValue = {
    userFullName: 'Test Name',
    clientName: 'Test Client',
    userEmail: 'test@name.com',
  }

  setConfig(contextValue)

  render(
    <RecoilRoot>
      <TopBarMenuUser />
    </RecoilRoot>
  )

  expect(screen.getByText(contextValue.userFullName)).toBeInTheDocument()
  expect(screen.getByText(contextValue.clientName)).toBeInTheDocument()
  expect(screen.getByText(contextValue.userEmail)).toBeInTheDocument()
})

test('renders a list item to link to profile', () => {
  render(
    <RecoilRoot>
      <TopBarMenuUser />
    </RecoilRoot>
  )

  const profileLink = screen.getByText('My Profile').closest('a')

  expect(profileLink).toHaveAttribute('href', PROFILE_PATH)
})

test('renders a list item to link to organization settings if canUserViewAdmin', () => {
  setConfig({ canUserViewAdmin: true })

  render(
    <RecoilRoot>
      <TopBarMenuUser />
    </RecoilRoot>
  )

  const orgSettingsLink = screen.getByText('Organization Settings').closest('a')

  expect(orgSettingsLink).toHaveAttribute('href', SETTINGS_GENERAL_PATH)
})

test('does not render a list item to link to organization settings if not canUserViewAdmin', () => {
  render(
    <RecoilRoot>
      <TopBarMenuUser />
    </RecoilRoot>
  )

  const orgSettingsListLitem = screen.queryByText('Organization Settings')

  expect(orgSettingsListLitem).toBeNull()
})

test('renders a list item to log out', () => {
  render(
    <RecoilRoot>
      <TopBarMenuUser />
    </RecoilRoot>
  )

  const logoutLink = screen.getByText('Logout').closest('a')

  expect(logoutLink).toHaveAttribute('href', LOGOUT_PATH)
})
