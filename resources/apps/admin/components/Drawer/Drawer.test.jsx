import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Drawer } from './Drawer'

test('returns null before toggleElementRef is clicked', () => {
  const toggleElementRef = document.createElement('button')
  const childrenTestId = 'childrenTestId'
  const Children = () => <input data-testid={childrenTestId} />

  render(
    <Drawer toggleElementRef={toggleElementRef}>
      <Children />
    </Drawer>
  )

  expect(screen.queryByTestId(childrenTestId)).toBeNull()
})

test('returns null when isOpen is passed as false', () => {
  const childrenTestId = 'childrenTestId'
  const Children = () => <input data-testid={childrenTestId} />

  render(
    <Drawer isOpen={false}>
      <Children />
    </Drawer>
  )

  expect(screen.queryByTestId(childrenTestId)).toBeNull()
})

test('renders given children when toggleElementRef is clicked', () => {
  const toggleElementRef = document.createElement('button')
  const childrenTestId = 'childrenTestId'
  const Children = () => <input data-testid={childrenTestId} />

  render(
    <Drawer toggleElementRef={toggleElementRef}>
      <Children />
    </Drawer>
  )

  userEvent.click(toggleElementRef)

  expect(screen.getByTestId(childrenTestId)).toBeInTheDocument()
})

test('renders given children when isOpen is passed as true', () => {
  const childrenTestId = 'childrenTestId'
  const Children = () => <input data-testid={childrenTestId} />

  render(
    <Drawer isOpen>
      <Children />
    </Drawer>
  )

  expect(screen.getByTestId(childrenTestId)).toBeInTheDocument()
})
