import { render, screen, act } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Dropdown } from './Dropdown'

jest.mock('@headlessui/react', () => ({
  ...jest.requireActual('@headlessui/react'),
  Transition: jest.fn(({ show, children }) => (show ? children : null)),
}))

test('opens when the toggleElement is clicked', async () => {
  const toggleElement = <div />
  const MenuContent = () => <div />

  render(<Dropdown toggleElement={toggleElement} menuContent={<MenuContent />} />)

  expect(screen.queryByLabelText('Dropdown menu content container')).toBeNull()

  const toggleContainer = screen.getByLabelText('Dropdown toggle container')

  await act(async () => {
    userEvent.click(toggleContainer)
  })

  expect(screen.queryByLabelText('Dropdown menu content container')).toBeInTheDocument()
})

test('closes when open and toggleElement is clicked', async () => {
  const toggleElement = <div />
  const MenuContent = () => <div />

  render(<Dropdown toggleElement={toggleElement} menuContent={<MenuContent />} />)

  const toggleContainer = screen.getByLabelText('Dropdown toggle container')

  await act(async () => {
    userEvent.click(toggleContainer)
  })

  expect(screen.queryByLabelText('Dropdown menu content container')).toBeInTheDocument()

  await act(async () => {
    userEvent.click(toggleContainer)
  })

  expect(screen.queryByLabelText('Dropdown menu content container')).toBeNull()
})
