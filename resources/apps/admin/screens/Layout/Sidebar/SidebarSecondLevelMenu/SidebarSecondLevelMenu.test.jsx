import { BrowserRouter } from 'react-router-dom'
import { render, screen, within } from '@testing-library/react'
import { RecoilRoot } from 'recoil'
import { SidebarSecondLevelMenu } from './SidebarSecondLevelMenu'

// eslint-disable-next-line react/prop-types
const Root = ({ children }) => (
  <BrowserRouter>
    <RecoilRoot>{children}</RecoilRoot>
  </BrowserRouter>
)

test('renders expected icon', () => {
  const title = 'Test Title'

  render(
    <Root>
      <SidebarSecondLevelMenu icon='user-friends' title={title} />
    </Root>
  )

  expect(screen.getByLabelText('user-friends')).toBeInTheDocument()
})

test('renders expected title', () => {
  const title = 'Test Title'

  render(
    <Root>
      <SidebarSecondLevelMenu icon='user-friends' title={title} />
    </Root>
  )

  const titleElement = screen.getByText(title)

  expect(titleElement).toBeInTheDocument()
  expect(titleElement.tagName).toEqual('H3')
})

test('renders expected link if newLink', () => {
  const newLink = {
    label: 'New Link',
    url: 'go/somewhere/new',
    is_external: true,
  }

  render(
    <Root>
      <SidebarSecondLevelMenu icon='user-friends' title='Test Title' newLink={newLink} />
    </Root>
  )

  const newLinkElement = screen.getByText(newLink.label).closest('a')

  expect(newLinkElement).toBeInTheDocument()
  expect(newLinkElement.tagName).toEqual('A')
  expect(newLinkElement).toHaveAttribute('href', newLink.url)
  expect(within(newLinkElement).getByLabelText('plus-circle')).toBeInTheDocument()
  expect(within(newLinkElement).getByLabelText('external-link')).toBeInTheDocument()
})

test('does not render a link if not newLink', () => {
  render(
    <Root>
      <SidebarSecondLevelMenu icon='user-friends' title='Test Title' />
    </Root>
  )

  expect(screen.queryByRole('link')).toBeNull()
})

test('does not render a link if newLink is empty', () => {
  render(
    <Root>
      <SidebarSecondLevelMenu icon='user-friends' title='Test Title' newLink={{}} />
    </Root>
  )

  expect(screen.queryByRole('link')).toBeNull()
})

test('builds out sections if menuItems is an object', () => {
  const menuItems = {
    section1: {
      label: 'Section One',
      children: [
        {
          label: 'Item One',
          url: 'some/url/one',
        },
        {
          label: 'Item Two',
          url: 'some/url/two',
        },
      ],
    },
  }

  render(
    <Root>
      <SidebarSecondLevelMenu icon='user-friends' title='Test Title' menuItems={menuItems} />
    </Root>
  )

  const sectionTitle = screen.getByText('Section One')

  expect(sectionTitle).toBeInTheDocument()
  expect(sectionTitle.tagName).toEqual('P')
  expect(screen.getByRole('list')).toBeInTheDocument()
  expect(screen.getAllByRole('listitem').length).toEqual(menuItems.section1.children.length)
})

test('builds out expected list if menuItems is an array', () => {
  const menuItems = [
    {
      label: 'Item One',
      url: 'some/url/one',
    },
    {
      label: 'Item Two',
      url: 'some/url/two',
    },
  ]

  render(
    <Root>
      <SidebarSecondLevelMenu icon='user-friends' title='Test Title' menuItems={menuItems} />
    </Root>
  )

  expect(screen.getByRole('list')).toBeInTheDocument()
  expect(screen.getAllByRole('listitem').length).toEqual(menuItems.length)
})
