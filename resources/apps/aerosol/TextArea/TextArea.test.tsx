import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { TextArea } from './TextArea'

describe('<TextArea/>', () => {
  let mockLabel: string
  let mockDisabled: boolean
  let mockIsOptional: boolean
  let mockCharCountMax: number | undefined
  let mockErrors: string[]

  const renderScreen = () => {
    render(
      <TextArea
        name='name'
        label={mockLabel}
        isOptional={mockIsOptional}
        isDisabled={mockDisabled}
        charCountMax={mockCharCountMax}
        errors={mockErrors}
      />
    )
    const textarea = screen.getByRole('textbox') as HTMLTextAreaElement
    return { textarea }
  }

  beforeEach(() => {
    mockCharCountMax = undefined
    mockLabel = 'statement'
    mockIsOptional = false
    mockDisabled = false
    mockErrors = []
  })

  it('should render the label if passed', () => {
    renderScreen()

    const label = screen.getByLabelText(mockLabel)

    expect(label).toBeInTheDocument()
  })

  it('should not render the optional label by default and textarea should have required attribute by default', () => {
    const { textarea } = renderScreen()

    const label = screen.queryByText('optional')

    expect(label).not.toBeInTheDocument()
    expect(textarea).toHaveAttribute('required')
  })

  it('should render the optional label if the prop is passed', () => {
    mockIsOptional = true

    const { textarea } = renderScreen()
    const label = screen.getByText('optional')

    expect(label).toBeInTheDocument()
    expect(textarea).not.toHaveAttribute('required')
  })

  it('should disable the textarea if the isDisabled prop is set to true', () => {
    mockDisabled = true

    const { textarea } = renderScreen()

    expect(textarea).toBeDisabled()
  })

  it('should allow the textarea to have max character count if the prop charCountMax has a value', () => {
    mockCharCountMax = 10

    const { textarea } = renderScreen()

    expect(textarea).toHaveAttribute('maxLength')
  })

  it('should not allow the user to add more then the charCountMax values in the textarea', () => {
    mockCharCountMax = 10

    const { textarea } = renderScreen()
    userEvent.type(textarea, 'onetwothree')

    expect(textarea.value).toHaveLength(10)
  })

  it('should render error message if the errors prop has been populated', () => {
    const message = 'email is wrong'
    mockErrors = [message]

    renderScreen()

    const error = screen.getByTestId('error-0')
    expect(error).toHaveTextContent(message)
  })
})
