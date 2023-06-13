import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { CheckboxGroup } from './CheckboxGroup'
import { Checkbox } from './Checkbox'
import { Text } from '../Text'

describe('<CheckboxGroup />', () => {
  let mockIsLabelVisible: boolean
  let mockIsDisabled: boolean
  const mockOnChange = jest.fn()
  let mockValues = { en_US: false, es_MX: false, fr_CA: false }

  beforeEach(() => {
    mockIsDisabled = false
    mockIsLabelVisible = false
  })

  const renderComponent = () =>
    render(
      <CheckboxGroup
        label='test'
        name='test'
        onChange={mockOnChange}
        values={mockValues}
        isLabelVisible={mockIsLabelVisible}
        isDisabled={mockIsDisabled}
      >
        <Checkbox id='en_US' value='en_US'>
          <Text type='h5' isMarginless>
            English
          </Text>
        </Checkbox>
        <Checkbox id='es_MX' value='es_MX'>
          <Text type='h5' isMarginless>
            Spanish
          </Text>
        </Checkbox>
        <Checkbox id='fr_CA' value='fr_CA'>
          <Text type='h5' isMarginless>
            French
          </Text>
        </Checkbox>
      </CheckboxGroup>
    )

  it('should render CheckboxGroup children', () => {
    renderComponent()

    expect(screen.getAllByRole('checkbox')).toHaveLength(3)
  })

  it('should render a visible label by default', () => {
    renderComponent()

    expect(screen.getByText('test', { selector: 'legend' })).toBeVisible()
  })

  it('should render a screen reader only label', () => {
    renderComponent()

    const label = screen.getByText('test', { selector: 'legend' })
    expect(label).toHaveClass('screenReader')
  })

  it('should call onChange when checkboxes are selected', () => {
    renderComponent()

    const checkboxes = screen.getAllByRole('checkbox')

    checkboxes.forEach((checkbox) => userEvent.click(checkbox))

    expect(mockOnChange).toHaveBeenCalledTimes(3)
  })

  it('should have checked attribute if all values are true', () => {
    mockValues = { en_US: true, es_MX: true, fr_CA: true }

    renderComponent()

    const checkboxes = screen.getAllByRole('checkbox')

    checkboxes.forEach((checkbox) => expect(checkbox).toBeChecked())
  })

  it('should disable all checkboxes if the isDisabled prop is true', () => {
    mockIsDisabled = true

    renderComponent()

    const checkboxes = screen.getAllByRole('checkbox')
    checkboxes.forEach((checkbox) => expect(checkbox).toBeDisabled())
  })
})
