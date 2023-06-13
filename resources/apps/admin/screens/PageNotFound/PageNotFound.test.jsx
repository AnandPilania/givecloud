import { render, screen } from '@testing-library/react'
import { PageNotFound } from './PageNotFound'

test('renders expected heading', () => {
  render(<PageNotFound />)

  const heading = screen.getByText('Error 404')

  expect(heading).toBeInTheDocument()
  expect(heading.tagName).toEqual('H2')
})

test('renders expected message', () => {
  render(<PageNotFound />)

  const message = screen.getByText('Resource not found')

  expect(message).toBeInTheDocument()
  expect(message.tagName).toEqual('H1')
})

test('renders expected description', () => {
  render(<PageNotFound />)

  const description = screen.getByText(
    'The requested resource could not be found but may be available again in the future.'
  )

  expect(description).toBeInTheDocument()
  expect(description.tagName).toEqual('H2')
})
