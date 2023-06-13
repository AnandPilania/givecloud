import type { IconDefinition } from '@fortawesome/pro-regular-svg-icons'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { faEnvelope, faExclamationCircle } from '@fortawesome/pro-regular-svg-icons'
import { Input } from './Input'

describe('<Input/>', () => {
  let mockLabel: string
  let mockDisabled: boolean
  let mockIsOptional: boolean
  let mockCharCountMax: number | undefined
  let mockErrors: string[]
  let mockIcon: IconDefinition | undefined

  const renderScreen = () => {
    render(
      <Input
        name='name'
        label={mockLabel}
        isOptional={mockIsOptional}
        isDisabled={mockDisabled}
        charCountMax={mockCharCountMax}
        errors={mockErrors}
        icon={mockIcon}
      />
    )
    const input = screen.getByRole('textbox') as HTMLInputElement
    return { input }
  }

  beforeEach(() => {
    mockCharCountMax = undefined
    mockLabel = 'email'
    mockIsOptional = false
    mockDisabled = false
    mockErrors = []
    mockIcon = undefined
  })

  it('should render the label if passed', () => {
    renderScreen()

    const label = screen.getByLabelText(mockLabel)

    expect(label).toBeInTheDocument()
  })

  it('should not render the optional label by default and input should have required attribute by default', () => {
    const { input } = renderScreen()

    const label = screen.queryByText('optional')

    expect(label).not.toBeInTheDocument()
    expect(input).toHaveAttribute('required')
  })

  it('should render the optional label if the prop is passed', () => {
    mockIsOptional = true

    const { input } = renderScreen()
    const label = screen.getByText('optional')

    expect(label).toBeInTheDocument()
    expect(input).not.toHaveAttribute('required')
  })

  it('should disable the input if the isDisabled prop is set to true', () => {
    mockDisabled = true

    const { input } = renderScreen()

    expect(input).toBeDisabled()
  })

  it('should allow the input to have max character count if the prop charCountMax has a value', () => {
    mockCharCountMax = 10

    const { input } = renderScreen()

    expect(input).toHaveAttribute('maxLength')
  })

  it('should not allow the user to add more then the charCountMax values in the input', () => {
    mockCharCountMax = 10

    const { input } = renderScreen()

    userEvent.type(input, 'onetwothree')

    expect(input.value.length).toBe(10)
  })

  it('should render error message if the errors prop has been populated', () => {
    const message = 'email is wrong'
    mockErrors = [message]

    renderScreen()

    const error = screen.getByTestId('error-0')
    expect(error).toHaveTextContent(message)
  })

  it('should render an icon within the input if the icon prop is populated', () => {
    mockIcon = faEnvelope

    renderScreen()

    expect(screen.getByLabelText(mockIcon.iconName)).toBeInTheDocument()
  })

  it('should overwrite a custom icon with the error icon if an error is active', () => {
    mockIcon = faEnvelope
    const errorIcon = faExclamationCircle.iconName
    const message = 'email is wrong'
    mockErrors = [message]

    renderScreen()

    expect(screen.queryByTitle(mockIcon.iconName)).not.toBeInTheDocument()
    expect(screen.getByTitle(errorIcon)).toBeInTheDocument()
  })
})
