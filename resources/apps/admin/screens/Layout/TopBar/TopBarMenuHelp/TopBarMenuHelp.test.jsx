import { render, screen, within } from '@testing-library/react'
import { RecoilRoot } from 'recoil'
import { TopBarMenuHelp } from '@/screens/Layout/TopBar/TopBarMenuHelp'
import { GIVECLOUD_HELP_URL, TRUSTRAISING_URL, CALENDLY_URL } from '@/constants/urlConstants'
import { setConfig } from '@/utilities/config'

const renderWithContext = (contextValue = {}) => {
  setConfig(contextValue)

  render(
    <RecoilRoot>
      <TopBarMenuHelp />
    </RecoilRoot>
  )
}

test('renders expected list by default', () => {
  render(
    <RecoilRoot>
      <TopBarMenuHelp />
    </RecoilRoot>
  )

  const list = screen.getByRole('list')

  const helpLink = within(list).getByText('Help Articles').closest('a')

  const trustraisingLink = within(list).getByText('Trustraising').closest('a')

  expect(within(list).getByText('Support')).toBeInTheDocument()
  expect(helpLink).toHaveAttribute('href', GIVECLOUD_HELP_URL)
  expect(trustraisingLink).toHaveAttribute('href', TRUSTRAISING_URL)
  expect(within(list).queryByText('Start a Live Chat')).toBeNull()
  expect(within(list).queryByText('Phone Support')).toBeNull()
  expect(within(list).queryByText('Toll Free:')).toBeNull()
  expect(within(list).queryByText('Request a Call')).toBeNull()
  expect(within(list).queryByText('View in MissionControl')).toBeNull()
})

test('renders live chat list item when canUserLiveChat', () => {
  renderWithContext({ canUserLiveChat: true })

  const list = screen.getByRole('list')

  expect(within(list).getByText('Start a Live Chat')).toBeInTheDocument()
})

test('renders phone support list item when siteSubscriptionSupportPhone is "request"', () => {
  renderWithContext({ siteSubscriptionSupportPhone: 'request' })

  const list = screen.getByRole('list')

  expect(within(list).getByText('Phone Support')).toBeInTheDocument()
})

test('renders phone support list item when siteSubscriptionSupportPhone is "direct"', () => {
  renderWithContext({ siteSubscriptionSupportPhone: 'direct' })

  const list = screen.getByRole('list')

  expect(within(list).getByText('Phone Support')).toBeInTheDocument()
})

test('renders toll free number if siteSubscriptionSupportPhone is "direct" and siteSubscriptionSupportDirectLine exists', () => {
  const siteSubscriptionSupportDirectLine = '1-888-888-8888'

  renderWithContext({
    siteSubscriptionSupportPhone: 'direct',
    siteSubscriptionSupportDirectLine,
  })

  const list = screen.getByRole('list')

  const link = within(list).getByText(`Toll Free: ${siteSubscriptionSupportDirectLine}`).closest('a')

  expect(link).toHaveAttribute('href', `tel:${siteSubscriptionSupportDirectLine}`)
})

test('renders request a call list item when siteSubscriptionSupportPhone is "request"', () => {
  renderWithContext({ siteSubscriptionSupportPhone: 'request' })

  const list = screen.getByRole('list')

  expect(within(list).getByText('Request a Call')).toBeInTheDocument()
})

test('renders request a call list item when siteSubscriptionSupportPhone is "direct"', () => {
  renderWithContext({ siteSubscriptionSupportPhone: 'direct' })

  const list = screen.getByRole('list')

  const link = within(list).getByText('Request a Call').closest('a')

  expect(link).toHaveAttribute('href', CALENDLY_URL)
})

test('renders a list item for Givecloud Support Team when isSuperUser', () => {
  renderWithContext({ isSuperUser: true })

  const list = screen.getByRole('list')

  expect(within(list).getByText('Givecloud Support Team')).toBeInTheDocument()
})

test('renders a View in MissionControl link when isSuperUser', () => {
  const clientMissionControlUrl = 'https://missioncontrol.givecloud.com'

  renderWithContext({ isSuperUser: true, clientMissionControlUrl })

  const list = screen.getByRole('list')

  const link = within(list).getByText('View in MissionControl').closest('a')

  expect(link).toHaveAttribute('href', clientMissionControlUrl)
})
