import { render, screen, within } from '@testing-library/react'
import { RecoilRoot } from 'recoil'
import { TopBarMenuUpdates } from '@/screens/Layout/TopBar/TopBarMenuUpdates'
import { GIVECLOUD_UPDATES_URL } from '@/constants/urlConstants'
import { setConfig } from '@/utilities/config'

const updates = [
  {
    id: 1,
    type: 'new',
    headline: 'Test Headline',
    summary: 'Test summary of update',
    is_beta: true,
  },
  {
    id: 2,
    type: 'patch',
    headline: 'Test Patch Headline',
    summary: 'Test summary of the patch',
    is_beta: false,
  },
]

const renderWithUpdates = () => {
  setConfig({ updates })

  render(
    <RecoilRoot>
      <TopBarMenuUpdates />
    </RecoilRoot>
  )
}

test('renders a list of updates when there are updates', () => {
  renderWithUpdates()

  expect(screen.getByRole('list')).toBeInTheDocument()
})

test('renders expected list items for updates', () => {
  renderWithUpdates()

  const listItems = screen.getAllByRole('listitem')

  expect(listItems).toHaveLength(updates.length)

  listItems.forEach((listItem, index) => {
    const headline = within(listItem).getByText(updates[index].headline)
    const type = within(listItem).getByText(updates[index].type.toUpperCase())
    const summary = within(listItem).getByText(updates[index].summary)

    expect(headline).toBeInTheDocument()
    expect(headline).toHaveAttribute('href', GIVECLOUD_UPDATES_URL)
    expect(type).toBeInTheDocument()
    expect(summary).toBeInTheDocument()
  })
})

test('list item renders beta tag if is_beta', () => {
  const updates = [
    {
      id: 1,
      type: 'new',
      headline: 'Test Headline',
      summary: 'Test summary of update',
      is_beta: true,
    },
  ]

  setConfig({ updates })

  render(
    <RecoilRoot>
      <TopBarMenuUpdates />
    </RecoilRoot>
  )

  const listItem = screen.getByRole('listitem')

  expect(within(listItem).getByText('BETA')).toBeInTheDocument()
})

test('list item does not render beta tag if not is_beta', () => {
  const updates = [
    {
      id: 1,
      type: 'new',
      headline: 'Test Headline',
      summary: 'Test summary of update',
      is_beta: false,
    },
  ]

  setConfig({ updates })

  render(
    <RecoilRoot>
      <TopBarMenuUpdates />
    </RecoilRoot>
  )

  const listItem = screen.getByRole('listitem')

  expect(within(listItem).queryByText('BETA')).toBeNull()
})

test('does not render a list if no updates', () => {
  render(
    <RecoilRoot>
      <TopBarMenuUpdates />
    </RecoilRoot>
  )

  expect(screen.queryByRole('list')).toBeNull()
})

test('renders no recent updates if no updates', () => {
  render(
    <RecoilRoot>
      <TopBarMenuUpdates />
    </RecoilRoot>
  )

  expect(screen.getByText('No Recent Updates')).toBeInTheDocument()
})

test('renders view all updates button', () => {
  render(
    <RecoilRoot>
      <TopBarMenuUpdates />
    </RecoilRoot>
  )

  const viewAllUpdatesButton = screen.getByText('View All Updates').closest('a')

  expect(viewAllUpdatesButton).toBeInTheDocument()
  expect(viewAllUpdatesButton).toHaveAttribute('href', GIVECLOUD_UPDATES_URL)
})
