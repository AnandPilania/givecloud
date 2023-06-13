import { CodeInput } from './CodeInput'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

describe('<CodeInput />', () => {
  const renderComponent = () => render(<CodeInput />)

  it('should render the code input with a max length', () => {
    renderComponent()

    const codeInput = screen.getByRole('textbox') as HTMLInputElement

    expect(codeInput.getAttribute('maxLength')).toBe('1')
  })

  it('should have a value that is one character in length if more than one character is typed', () => {
    renderComponent()

    const codeInput = screen.getByRole('textbox') as HTMLInputElement

    userEvent.type(codeInput, 'many chars')

    expect(codeInput.value.length).toBe(1)
  })
})
