import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Checkbox } from './Checkbox'
import { Text } from '../../Text'

describe('<Checkbox />', () => {
  let mockDisabled: boolean
  const mockOnChange = jest.fn()
  let mockValues = { en_US: false }

  beforeEach(() => (mockDisabled = false))

  const renderComponent = () =>
    render(
      <Checkbox disabled={mockDisabled} id='test' value='en_US' values={mockValues} onChange={mockOnChange}>
        <Text type='h5' isMarginless>
          English
        </Text>
      </Checkbox>
    )

  it('should call onChange when checkbox is selected', () => {
    renderComponent()

    const checkbox = screen.getByRole('checkbox')

    userEvent.click(checkbox)

    expect(mockOnChange).toHaveBeenCalled()
  })

  it('should have checked attribute if all values are true', () => {
    mockValues = { en_US: true }

    renderComponent()

    const checkbox = screen.getByRole('checkbox')

    userEvent.click(checkbox)

    expect(mockOnChange).toHaveBeenCalled()
  })

  it('should disable if the disabled prop is true', () => {
    mockDisabled = true

    renderComponent()

    expect(screen.getByRole('checkbox')).toBeDisabled()
  })
})
