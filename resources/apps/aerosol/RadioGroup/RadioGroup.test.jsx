import { RadioGroup } from './RadioGroup'
import { RadioButton } from './RadioButton'
import { render, screen, fireEvent } from '@testing-library/react'

describe('<RadioGroup />', () => {
  let isLabelVisible
  let onChange

  const renderScreen = () =>
    render(
      <RadioGroup
        label='Test Group'
        isLabelVisible={isLabelVisible}
        onChange={onChange}
        name='test'
        checkedValue='option1'
      >
        <RadioButton
          id='option1'
          name='test'
          disabled={false}
          label='Label 1'
          description='Description 1'
          value='option1'
        />
        <RadioButton
          id='option2'
          name='test'
          disabled={false}
          label='Label 2'
          description='Description 2'
          value='option2'
        />
      </RadioGroup>
    )

  it('should render RadioButton children', () => {
    renderScreen()

    const radioButtons = screen.getAllByRole('radio')
    expect(radioButtons).toHaveLength(2)
  })

  it('should render a visible label by default', () => {
    renderScreen()

    const label = screen.getByText('Test Group', { selector: 'legend' })
    expect(label).toBeVisible()
  })

  it('should render a screen reader only label', () => {
    isLabelVisible = false
    renderScreen()

    const label = screen.getByText('Test Group', { selector: 'legend' })
    expect(label).toHaveClass('screenReader')
  })

  it('should call the onChange callback when another option is selected', () => {
    onChange = jest.fn()
    renderScreen()

    const radioButtonLabel = screen.getByText('Label 2')
    fireEvent.click(radioButtonLabel)
    expect(onChange).toHaveBeenCalled()
  })
})
