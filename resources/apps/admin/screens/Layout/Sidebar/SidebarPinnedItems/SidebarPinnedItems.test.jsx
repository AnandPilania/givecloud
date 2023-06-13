import { render, screen, within } from '@testing-library/react'
import { SidebarPinnedItems } from './SidebarPinnedItems'
import { PROFILE_PINNED_ITEMS_PATH } from '@/constants/pathConstants'
import { RecoilRoot } from 'recoil'
import { setConfig } from '@/utilities/config'

const pinnedMenuItems = [
  {
    key: 'one',
    label: 'First Pinned Item',
    icon: 'plus',
    url: '/one',
    is_external: false,
  },
  {
    key: 'two',
    label: 'Second Pinned Item',
    icon: 'home',
    url: '/two',
    is_external: true,
  },
]

test('renders expected heading', () => {
  render(
    <RecoilRoot>
      <SidebarPinnedItems />
    </RecoilRoot>
  )

  const heading = screen.getByText('Pinned')

  expect(heading).toBeInTheDocument()
  expect(heading.tagName).toEqual('P')
})

test('renders a list for pinned items if there are pinnedMenuItems', () => {
  setConfig({ pinnedMenuItems })

  render(
    <RecoilRoot>
      <SidebarPinnedItems />
    </RecoilRoot>
  )

  expect(screen.getByRole('list')).toBeInTheDocument()
})

test('renders expected pinned items based on pinnedMenuItems', () => {
  setConfig({ pinnedMenuItems })

  render(
    <RecoilRoot>
      <SidebarPinnedItems />
    </RecoilRoot>
  )

  pinnedMenuItems.forEach((pin) => {
    const link = screen.getByText(pin.label).closest('a')

    expect(link).toBeInTheDocument()
    expect(link.tagName).toEqual('A')
    expect(link).toHaveAttribute('href', pin.url)

    if (pin.is_external) {
      expect(link).toHaveAttribute('target', '_blank')
      expect(link).toHaveAttribute('rel', 'noopener noreferrer')
    } else {
      expect(link).not.toHaveAttribute('target')
      expect(link).not.toHaveAttribute('rel')
    }

    expect(within(link).getByLabelText(pin.icon)).toBeInTheDocument()

    expect(screen.getAllByLabelText('external-link').length).toEqual(
      pinnedMenuItems.filter(({ is_external }) => !!is_external).length
    )
  })
})

test('does not render a list for pinned items if there are no pinnedMenuItems', () => {
  setConfig({ pinnedMenuItems: [] })

  render(
    <RecoilRoot>
      <SidebarPinnedItems />
    </RecoilRoot>
  )

  expect(screen.queryByRole('list')).toBeNull()
})

test('renders expected edit link', () => {
  render(
    <RecoilRoot>
      <SidebarPinnedItems />
    </RecoilRoot>
  )

  const link = screen.getByText('Add / Remove Pins').closest('a')

  expect(link).toBeInTheDocument()
  expect(link).toHaveAttribute('href', PROFILE_PINNED_ITEMS_PATH)
})
