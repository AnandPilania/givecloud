import { render, screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { TopBarMenuItem } from '@/screens/Layout/TopBar/TopBarMenuItem'

afterEach(() => {
  jest.clearAllMocks()
})

test('renders Dropdown with expected menuContent', () => {
  const MenuContent = () => <div data-testid='content'>hello</div>

  render(
    <TopBarMenuItem icon='home'>
      <MenuContent />
    </TopBarMenuItem>
  )

  const button = screen.getByRole('button')
  userEvent.click(button)

  expect(screen.getByTestId('content')).toBeInTheDocument()
})

test('renders a label if given', () => {
  const label = 'Test Label'
  const MenuContent = () => <div />

  render(
    <TopBarMenuItem icon='home' label={label}>
      <MenuContent />
    </TopBarMenuItem>
  )

  const labelElement = screen.getByTestId('label')

  expect(labelElement).toBeInTheDocument()
  expect(within(labelElement).getByText(label)).toBeInTheDocument()
})

test('does not render a label if not given', () => {
  render(
    <TopBarMenuItem icon='home'>
      <div />
    </TopBarMenuItem>
  )

  expect(screen.queryByTestId('label')).not.toBeInTheDocument()
})

test('renders a badge if given', () => {
  const badge = 2

  render(
    <TopBarMenuItem icon='home' badge={badge}>
      <div />
    </TopBarMenuItem>
  )

  const badgeElement = screen.getByTestId('badge')

  expect(badgeElement).toBeInTheDocument()
  expect(within(badgeElement).getByText(badge)).toBeInTheDocument()
})

test('does not render a badge if not given', () => {
  render(
    <TopBarMenuItem icon='home'>
      <div />
    </TopBarMenuItem>
  )

  expect(screen.queryByTestId('badge')).not.toBeInTheDocument()
})

test('renders a down icon if no badge', () => {
  render(
    <TopBarMenuItem icon='home'>
      <div />
    </TopBarMenuItem>
  )

  expect(screen.getByLabelText('chevron-down')).toBeInTheDocument()
})

test('does not render a down icon if badge', () => {
  render(
    <TopBarMenuItem icon='home' badge={2}>
      <div />
    </TopBarMenuItem>
  )

  expect(screen.queryByLabelText('chevron-down')).not.toBeInTheDocument()
})
