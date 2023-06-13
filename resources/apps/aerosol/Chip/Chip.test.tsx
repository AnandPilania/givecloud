import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Chip } from './Chip'

jest.mock('react-emoji-render', () => jest.fn(() => null))

describe('<Chip />', () => {
  let href: string | undefined
  let onClick: jest.Mock<any, any> | undefined

  const renderScreen = () =>
    render(
      <Chip href={href} onClick={onClick}>
        Test Chip
      </Chip>
    )

  beforeEach(() => {
    href = undefined
    onClick = undefined
  })

  it('renders an anchor if provided an href', () => {
    href = 'https://www.some-url.com'
    renderScreen()

    const link = screen.getByRole('link')

    expect(link).toBeInTheDocument()
    expect(link).toHaveAttribute('href', href)
  })

  it('renders a button if provided an onClick', () => {
    onClick = jest.fn()
    renderScreen()

    const button = screen.getByRole('button')
    expect(button).toBeInTheDocument()
  })

  it('renders a div if not provided an href or onClick', () => {
    renderScreen()

    const chip = screen.getByTestId('Chip')

    expect(chip).toBeInTheDocument()
    expect(chip.tagName).toEqual('DIV')
  })

  it('renders given children', () => {
    const childrenTestId = 'childrenTest'

    render(
      <Chip>
        <div data-testid={childrenTestId} />
      </Chip>
    )

    expect(screen.getByTestId(childrenTestId)).toBeInTheDocument()
  })

  it('fires given onClick and element is a button', () => {
    onClick = jest.fn()
    renderScreen()

    const button = screen.getByRole('button')
    userEvent.click(button)

    expect(onClick.mock.calls).toHaveLength(1)
  })
})
