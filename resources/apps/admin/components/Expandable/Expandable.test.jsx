import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Expandable } from './Expandable'

const contentHeight = 100
const originalScrollHeight = Object.getOwnPropertyDescriptor(HTMLElement.prototype, 'scrollHeight')

/*
All elements in the jest dom are considered to have 0 height.
Before the test suite runs, we mock the scrollHeight getter
to return a height that we can assert against.
*/
beforeAll(() => {
  Object.defineProperties(window.HTMLElement.prototype, {
    scrollHeight: {
      configurable: true,
      get: () => contentHeight,
    },
  })
})

/*
After the test suite runs, we want to restore the scrollHeight
getter back to the original so that this "mock" is scoped to this
test suite only.
*/
afterAll(() => {
  Object.defineProperties(window.HTMLElement.prototype, {
    scrollHeight: {
      configurable: true,
      get: () => originalScrollHeight,
    },
  })
})

test('renders given children', () => {
  const toggleElementRef = document.createElement('button')
  const childrenTestId = 'testChild'
  const children = <div data-testid={childrenTestId}></div>

  render(<Expandable toggleElementRef={toggleElementRef}>{children}</Expandable>)

  expect(screen.getByTestId(childrenTestId)).toBeInTheDocument()
})

test('expands when not isExpanded and toggleElementRef is clicked', () => {
  const toggleElementRef = document.createElement('button')

  render(
    <Expandable toggleElementRef={toggleElementRef}>
      <></>
    </Expandable>
  )

  const expandableContent = screen.getByLabelText('Expandable content')

  expect(expandableContent.style.height).toEqual('0px')

  userEvent.click(toggleElementRef)

  expect(expandableContent.style.height).toEqual(`${contentHeight}px`)
})

test('does not expand when isDisabled even when not isExpanded and toggleElementRef is clicked', () => {
  const toggleElementRef = document.createElement('button')

  render(
    <Expandable toggleElementRef={toggleElementRef} isDisabled>
      <></>
    </Expandable>
  )

  const expandableContent = screen.getByLabelText('Expandable content')

  expect(expandableContent.style.height).toEqual('0px')

  userEvent.click(toggleElementRef)

  expect(expandableContent.style.height).toEqual('0px')
})

test('collapses when isExpanded and toggleElementRef is clicked', () => {
  const toggleElementRef = document.createElement('button')

  render(
    <Expandable toggleElementRef={toggleElementRef} isExpandedInitially>
      <></>
    </Expandable>
  )

  const expandableContent = screen.getByLabelText('Expandable content')

  expect(expandableContent.style.height).toEqual(`${contentHeight}px`)

  userEvent.click(toggleElementRef)

  expect(expandableContent.style.height).toEqual('0px')
})

test('does not collapse when isDisabled even when isExpanded and toggleElementRef is clicked', () => {
  const toggleElementRef = document.createElement('button')

  render(
    <Expandable toggleElementRef={toggleElementRef} isExpandedInitially isDisabled>
      <></>
    </Expandable>
  )

  const expandableContent = screen.getByLabelText('Expandable content')

  expect(expandableContent.style.height).toEqual(`${contentHeight}px`)

  userEvent.click(toggleElementRef)

  expect(expandableContent.style.height).toEqual(`${contentHeight}px`)
})

test('is expanded if given static isExpanded prop as true', () => {
  render(
    <Expandable isExpanded>
      <></>
    </Expandable>
  )

  const expandableContent = screen.getByLabelText('Expandable content')

  expect(expandableContent.style.height).toEqual(`${contentHeight}px`)
})

test('is not expanded if given static isExpanded prop as false', () => {
  render(
    <Expandable isExpanded={false}>
      <></>
    </Expandable>
  )

  const expandableContent = screen.getByLabelText('Expandable content')

  expect(expandableContent.style.height).toEqual(`0px`)
})
