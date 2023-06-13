import { AmountSelector } from './AmountSelector'
import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

describe('<AmountSelector />', () => {
  let mockValue = 100
  let mockMaxValue: number
  const mockOnChange = jest.fn()
  const mockPresetAmounts = [50, 75, 100, 125]
  const mockCurrency = 'USD'

  const renderComponent = () =>
    render(
      <AmountSelector
        value={mockValue}
        onChange={mockOnChange}
        currency={mockCurrency}
        presetAmounts={mockPresetAmounts}
        maxValue={mockMaxValue}
      />
    )

  beforeEach(() => {
    jest.clearAllMocks()
  })

  it('should render with a default value', () => {
    renderComponent()

    expect(screen.getByLabelText(`$${mockValue}`)).toBeInTheDocument()
  })

  it('should update the displayed amount when plus(+) button is clicked', () => {
    renderComponent()

    userEvent.click(screen.getByRole('button', { name: 'increase amount' }))

    expect(mockOnChange).toHaveBeenCalledWith(125)
  })

  it('should update the displayed amount when minus(-) is clicked', () => {
    renderComponent()

    userEvent.click(screen.getByRole('button', { name: 'decrease amount' }))

    expect(mockOnChange).toHaveBeenCalledWith(75)
  })

  it('should not update when current value is the maximum preset amount and the plus(+) button is clicked', () => {
    mockValue = 125

    renderComponent()

    const increaseButton = screen.getByRole('button', { name: 'increase amount' })
    userEvent.click(increaseButton)

    expect(mockOnChange).not.toHaveBeenCalled()
    expect(increaseButton).toHaveAttribute('aria-disabled', 'true')
  })

  it('should not update when the current value is the minimum preset amount and the minus(-) button is clicked', () => {
    mockValue = 50

    renderComponent()

    const decreaseButton = screen.getByRole('button', { name: 'decrease amount' })
    userEvent.click(decreaseButton)

    expect(mockOnChange).not.toHaveBeenCalled()
    expect(decreaseButton).toHaveAttribute('aria-disabled', 'true')
  })

  it('should not update when the maximum value has been reached and the plus(+) button is clicked', () => {
    mockValue = 75
    mockMaxValue = 100

    renderComponent()

    const increaseButton = screen.getByRole('button', { name: 'increase amount' })
    userEvent.click(increaseButton)

    expect(mockOnChange).toHaveBeenCalledWith(100)
    waitFor(() => expect(increaseButton).toHaveAttribute('aria-disabled', 'true'))
  })

  it('should not update when the minimum value has been reached and the minus(-) button is clicked', () => {
    mockValue = 75

    renderComponent()

    const decreaseButton = screen.getByRole('button', { name: 'decrease amount' })
    userEvent.click(decreaseButton)

    expect(mockOnChange).toHaveBeenCalledWith(50)
    waitFor(() => expect(decreaseButton).toHaveAttribute('aria-disabled', 'true'))
  })
})
