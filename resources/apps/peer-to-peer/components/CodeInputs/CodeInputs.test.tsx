import { CodeInputs } from './CodeInputs'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

describe('<CodeInputs/>', () => {
  const mockInputValues = [
    {
      name: 'one',
      value: '',
    },
    {
      name: 'two',
      value: '',
    },
    {
      name: 'three',
      value: '',
    },
    {
      name: 'four',
      value: '',
    },
  ]

  const mockedNextFocusedElement = jest.fn()

  const renderComponent = () =>
    render(<CodeInputs inputValues={mockInputValues} nextFocusedElement={mockedNextFocusedElement} />)

  it('should render the same number of code inputs as there are input values', () => {
    renderComponent()

    expect(screen.getAllByRole('textbox')).toHaveLength(mockInputValues.length)
  })

  it('should focus on the first code input by default', () => {
    renderComponent()

    const codeInputs = screen.getAllByRole('textbox')

    expect(codeInputs[0]).toHaveFocus()
  })

  it('should focus on the next code input after typing', () => {
    renderComponent()

    const codeInputs = screen.getAllByRole('textbox')

    userEvent.type(codeInputs[0], 'A')
    expect(codeInputs[1]).toHaveFocus()

    userEvent.type(codeInputs[1], 'B')
    expect(codeInputs[2]).toHaveFocus()

    userEvent.type(codeInputs[2], 'C')
    expect(codeInputs[3]).toHaveFocus()
  })

  it('should call the function to focus on the next element after typing in the last code input', () => {
    renderComponent()

    const lastInput = screen.getAllByRole('textbox')[mockInputValues.length - 1]

    userEvent.type(lastInput, 'D')

    expect(mockedNextFocusedElement).toHaveBeenCalled()
  })
})
