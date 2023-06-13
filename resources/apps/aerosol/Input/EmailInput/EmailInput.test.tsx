import { fireEvent, render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { EmailInput } from './EmailInput'

let mockValue: string
let mockOnChange: () => void

describe('<EmailInput/>', () => {
  beforeEach(() => {
    mockValue = ''
    mockOnChange = jest.fn()
  })

  const renderScreen = () => {
    render(<EmailInput onChange={mockOnChange} value={mockValue} name='email' />)
    const input = screen.getByRole('textbox')
    return { input }
  }

  it('should render an email input', () => {
    const { input } = renderScreen()

    expect(input).toHaveAttribute('type', 'email')
  })

  it('should not throw an error on focus or onChange', () => {
    const { input } = renderScreen()

    userEvent.type(input, 'hello')

    expect(screen.queryByTestId('error-0')).not.toBeInTheDocument()
  })

  it('should throw an error onBlur', () => {
    mockValue = 'hello'
    const { input } = renderScreen()

    userEvent.type(input, mockValue)
    fireEvent.focusOut(input)

    expect(screen.getByTestId('error-0')).toBeInTheDocument()
  })

  it('should throw an error if email is missing an @', () => {
    mockValue = 'hello'

    const { input } = renderScreen()

    fireEvent.focusOut(input)

    expect(screen.getByTestId('error-0')).toHaveTextContent('@ is required')
  })

  it('should throw an error if email is missing an Dot', () => {
    mockValue = 'hello@'

    const { input } = renderScreen()

    fireEvent.focusOut(input)

    expect(screen.getByTestId('error-0')).toHaveTextContent('Dot is required')
  })

  it('should throw an error if email is not of valid format', () => {
    mockValue = 'hello@.com'

    const { input } = renderScreen()

    fireEvent.focusOut(input)

    expect(screen.getByTestId('error-0')).toHaveTextContent('Not a valid email')
  })

  it('should not trow an error if email is valid format', () => {
    mockValue = 'hello@world.com'

    const { input } = renderScreen()

    fireEvent.focusOut(input)

    expect(screen.queryByTestId('error-0')).not.toBeInTheDocument()
  })
})
