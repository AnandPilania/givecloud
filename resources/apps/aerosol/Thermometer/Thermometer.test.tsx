import { Thermometer } from './Thermometer'
import { render, screen } from '@testing-library/react'

describe('<Thermometer />', () => {
  let mockInitialPercent: number
  let mockAdditionalPercent: number

  const renderComponent = () =>
    render(<Thermometer initialPercentage={mockInitialPercent} additionalPercentage={mockAdditionalPercent} />)

  it('should render when no percentages are given', () => {
    mockInitialPercent = 0
    mockAdditionalPercent = 0

    renderComponent()

    expect(screen.getByRole('progressbar')).toBeInTheDocument()
  })

  it('should display with the initial percentage', () => {
    mockInitialPercent = 10

    renderComponent()

    const initProgress = screen.getByTestId('initial-progress')
    expect(initProgress.getAttribute('aria-valuetext')).toBe(`${mockInitialPercent}% raised`)
  })

  it('should display when additional percentage is given', () => {
    mockAdditionalPercent = 50

    renderComponent()

    const additionalProgress = screen.getByTestId('additional-progress')
    expect(additionalProgress.getAttribute('aria-valuetext')).toBe(
      `you'll be adding ${mockAdditionalPercent}% to the total`
    )
  })
})
