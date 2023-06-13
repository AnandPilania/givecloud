import React from 'react'
import { BrowserRouter } from 'react-router-dom'
import { RecoilRoot } from 'recoil'
import { render, screen, within } from '@testing-library/react'
import { SidebarSecondLevelMenuItem } from './SidebarSecondLevelMenuItem'

afterEach(() => {
  jest.clearAllMocks()
})

// eslint-disable-next-line react/prop-types
const Root = ({ children }) => (
  <BrowserRouter>
    <RecoilRoot>{children}</RecoilRoot>
  </BrowserRouter>
)

test('renders a link with given label but no expand icon if no subMenuItems', () => {
  const label = 'Test Label'
  const url = 'some/url'

  render(
    <Root>
      <SidebarSecondLevelMenuItem label={label} url={url} />
    </Root>
  )

  const link = screen.getByText(label).closest('a')

  expect(link).toBeInTheDocument()
  expect(link.tagName).toEqual('A')
  expect(link).toHaveAttribute('href', url)
  expect(within(link).queryByLabelText('chevron-down')).not.toBeInTheDocument()
})

test('renders a link with expand icon if subMenuItems', () => {
  const label = 'Test Label'

  const subMenuItems = [
    {
      key: 'one',
      label: 'Label One',
      url: 'to/one',
    },
    {
      key: 'two',
      label: 'Label Two',
      url: 'to/two',
    },
  ]

  render(
    <Root>
      <SidebarSecondLevelMenuItem label={label} subMenuItems={subMenuItems} />
    </Root>
  )

  const link = screen.getByText(label).closest('a')

  expect(within(link).queryByLabelText('chevron-down')).toBeInTheDocument()
})

test('renders an Expandable that is not expanded by default if subMenuItems', () => {
  const subMenuItems = [
    {
      key: 'one',
      label: 'Label One',
      url: 'to/one',
    },
    {
      key: 'two',
      label: 'Label Two',
      url: 'to/two',
    },
  ]

  render(
    <Root>
      <SidebarSecondLevelMenuItem label='Test Label' subMenuItems={subMenuItems} />
    </Root>
  )

  expect(screen.getByTestId('expandable')).toBeInTheDocument()
})

test('calls toggleIsSubMenuExpanded initially if subMenuItems and isActive', () => {
  const toggleIsSubMenuExpanded = jest.fn()

  const subMenuItems = [
    {
      key: 'one',
      label: 'Label One',
      url: 'to/one',
    },
    {
      key: 'two',
      label: 'Label Two',
      url: 'to/two',
    },
  ]

  render(
    <Root>
      <SidebarSecondLevelMenuItem
        label='Test Label'
        icon='user-friends'
        subMenuItems={subMenuItems}
        isActive
        toggleIsSubMenuExpanded={toggleIsSubMenuExpanded}
      />
    </Root>
  )

  expect(toggleIsSubMenuExpanded).toHaveBeenCalled()
})

test('does not call toggleIsSubMenuExpanded initially if no subMenuItems', () => {
  const toggleIsSubMenuExpanded = jest.fn()

  render(
    <Root>
      <SidebarSecondLevelMenuItem label='Test Label' isActive toggleIsSubMenuExpanded={toggleIsSubMenuExpanded} />
    </Root>
  )

  expect(toggleIsSubMenuExpanded).not.toHaveBeenCalled()
})

test('does not call toggleIsSubMenuExpanded initially if not isActive', () => {
  const toggleIsSubMenuExpanded = jest.fn()

  const subMenuItems = [
    {
      key: 'one',
      label: 'Label One',
      url: 'to/one',
    },
    {
      key: 'two',
      label: 'Label Two',
      url: 'to/two',
    },
  ]

  render(
    <Root>
      <SidebarSecondLevelMenuItem
        label='Test Label'
        subMenuItems={subMenuItems}
        toggleIsSubMenuExpanded={toggleIsSubMenuExpanded}
      />
    </Root>
  )

  expect(toggleIsSubMenuExpanded).not.toHaveBeenCalled()
})

test('renders expected list based on subMenuItems', () => {
  const subMenuItems = [
    {
      key: 'one',
      label: 'Label One',
      url: 'to/one',
      is_external: false,
    },
    {
      key: 'two',
      label: 'Label Two',
      url: 'to/two',
      is_external: true,
    },
  ]

  render(
    <Root>
      <SidebarSecondLevelMenuItem label='Test Label' subMenuItems={subMenuItems} />
    </Root>
  )

  const list = screen.getByRole('list')

  expect(list).toBeInTheDocument()

  subMenuItems.forEach(({ label, url, is_external }) => {
    const link = screen.getByText(label).closest('a')
    const externalLinkIcon = within(link).queryByLabelText('external-link')

    expect(link).toBeInTheDocument()
    expect(link.tagName).toEqual('A')
    expect(link).toHaveAttribute('href', url)

    if (is_external) {
      expect(externalLinkIcon).toBeInTheDocument()
    } else {
      expect(externalLinkIcon).not.toBeInTheDocument()
    }
  })
})
