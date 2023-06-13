import { render, screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { RecoilRoot } from 'recoil'
import { Drawer } from '@/components/Drawer'
import { SidebarFirstLevelMenuItem } from './SidebarFirstLevelMenuItem'
import { SidebarSecondLevelMenu } from '@/screens/Layout/Sidebar/SidebarSecondLevelMenu'
import { MockComponent } from '@/utilities/MockComponent'

jest.mock('@/components/Drawer', () => ({
  Drawer: jest.fn((props) => MockComponent(props)),
}))

jest.mock('@/screens/Layout/Sidebar/SidebarSecondLevelMenu', () => ({
  SidebarSecondLevelMenu: jest.fn((props) => MockComponent(props)),
}))

afterEach(() => {
  jest.clearAllMocks()
})

test('renders a list item as the menu item', () => {
  render(
    <RecoilRoot>
      <SidebarFirstLevelMenuItem label='Test Label' />
    </RecoilRoot>
  )

  expect(screen.getByRole('listitem')).toBeInTheDocument()
})

test('does not render a Drawer by default', () => {
  render(
    <RecoilRoot>
      <SidebarFirstLevelMenuItem label='Test Label' />
    </RecoilRoot>
  )

  expect(Drawer).not.toHaveBeenCalled()
})

test('renders a label with the expected link', () => {
  const url = '/some/url'

  render(
    <RecoilRoot>
      <SidebarFirstLevelMenuItem label='Test Label' url={url} />
    </RecoilRoot>
  )

  const listItem = screen.getByRole('listitem')
  const link = within(listItem).getByRole('link')

  expect(link).toBeInTheDocument()
  expect(link).toHaveAttribute('href', url)
})

test('renders a label/link with a pill', () => {
  const url = 'https://testsite.com'

  render(
    <RecoilRoot>
      <SidebarFirstLevelMenuItem label='Test Label' url={url} pillLabel='Pill Label' />
    </RecoilRoot>
  )

  const pill = screen.getByTestId('pill')
  expect(pill).toBeInTheDocument()
})

test('does not render a SidebarSecondLevelMenu by default', () => {
  render(
    <RecoilRoot>
      <SidebarFirstLevelMenuItem label='Test Label' secondLevelMenuItems={[1]} />
    </RecoilRoot>
  )

  expect(SidebarSecondLevelMenu).not.toHaveBeenCalled()
})

test('renders a SidebarSecondLevelMenu if isFlyoutOpen is true', () => {
  render(
    <RecoilRoot>
      <SidebarFirstLevelMenuItem label='Test Label' url='test/url' secondLevelMenuItems={[1]} isFlyoutOpen />
    </RecoilRoot>
  )

  expect(SidebarSecondLevelMenu).toHaveBeenCalled()
})

test('calls given toggleIsFlyoutOpen function when link is clicked if not isMobile and hasSecondLevelMenuItems', () => {
  const toggleIsFlyoutOpen = jest.fn()

  render(
    <RecoilRoot>
      <SidebarFirstLevelMenuItem
        label='Test Label'
        url='test/url'
        secondLevelMenuItems={[1]}
        toggleIsFlyoutOpen={toggleIsFlyoutOpen}
      />
    </RecoilRoot>
  )

  userEvent.click(screen.getByRole('link'))

  expect(toggleIsFlyoutOpen).toHaveBeenCalled()
})

test('does not call given toggleIsFlyoutOpen function when link is clicked if isMobile', () => {
  const toggleIsFlyoutOpen = jest.fn()

  render(
    <RecoilRoot>
      <SidebarFirstLevelMenuItem
        label='Test Label'
        url='test/url'
        secondLevelMenuItems={[1]}
        toggleIsFlyoutOpen={toggleIsFlyoutOpen}
        isMobile
      />
    </RecoilRoot>
  )

  userEvent.click(screen.getByRole('link'))

  expect(toggleIsFlyoutOpen).not.toHaveBeenCalled()
})

test('calls given toggleIsFlyoutOpen function to close when not hasSecondLevelMenuItems', () => {
  const toggleIsFlyoutOpen = jest.fn()

  render(
    <RecoilRoot>
      <SidebarFirstLevelMenuItem label='Test Label' url='test/url' toggleIsFlyoutOpen={toggleIsFlyoutOpen} />
    </RecoilRoot>
  )

  userEvent.click(screen.getByRole('link'))

  expect(toggleIsFlyoutOpen).toHaveBeenCalled()

  const flyoutPanel = screen.queryByLabelText('Flyout Panel')
  expect(flyoutPanel).not.toBeInTheDocument()
})
