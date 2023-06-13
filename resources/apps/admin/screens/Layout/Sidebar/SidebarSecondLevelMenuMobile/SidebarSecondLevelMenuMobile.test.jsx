import { render, screen } from '@testing-library/react'
import { SidebarSecondLevelMenuMobile } from '@/screens/Layout/Sidebar/SidebarSecondLevelMenuMobile'
import { SidebarSecondLevelMenu } from '@/screens/Layout/Sidebar/SidebarSecondLevelMenu'
import { MockComponent } from '@/utilities/MockComponent'

jest.mock('@/screens/Layout/Sidebar/SidebarSecondLevelMenu', () => ({
  SidebarSecondLevelMenu: jest.fn((props) => MockComponent(props)),
}))

afterEach(() => {
  jest.clearAllMocks()
})

test('renders a close button', () => {
  render(<SidebarSecondLevelMenuMobile icon='users' title='Test Title' />)

  expect(screen.getByLabelText('Close button')).toBeInTheDocument()
})

test('renders SidebarSecondLevelMenu with given label, newLink, and menuItems', () => {
  const label = 'Test Label'

  const newLink = {
    label: 'New',
    url: '/path/new',
  }

  const menuItems = [
    {
      label: 'Test Label 1',
      url: 'some/url/1',
    },
    {
      label: 'Test Label 2',
      url: 'some/url/2',
    },
  ]

  render(<SidebarSecondLevelMenuMobile icon='users' title={label} newLink={newLink} menuItems={menuItems} />)

  const sidebarSecondLevelMenuInstances = SidebarSecondLevelMenu.mock.calls
  const props = sidebarSecondLevelMenuInstances[0][0]

  expect(sidebarSecondLevelMenuInstances.length).toEqual(1)
  expect(props).toMatchObject({ title: label, newLink, menuItems })
})
