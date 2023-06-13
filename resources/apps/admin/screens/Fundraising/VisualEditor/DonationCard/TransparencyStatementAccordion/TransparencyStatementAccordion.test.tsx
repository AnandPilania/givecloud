import { render, screen } from '@/mocks/setup'
import userEvent from '@testing-library/user-event'
import { TransparencyStatementAccordion } from './TransparencyStatementAccordion'

describe('<TransparencyStatementAccordion', () => {
  let mockIsOpen: boolean
  let mockSetIsOpen: () => void

  beforeEach(() => {
    mockIsOpen = true
    mockSetIsOpen = jest.fn()
  })

  const mockComponent = () => render(<TransparencyStatementAccordion isOpen={mockIsOpen} setIsOpen={mockSetIsOpen} />)

  it('should enable the input when toggle is on', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByRole('switch')).toBeChecked()

    const statementInput = screen.getByRole('textbox')

    expect(statementInput).toBeEnabled()
    expect(statementInput).toBeValid()

    expect(screen.queryByText('Field is required')).not.toBeInTheDocument()
  })

  it('should display an error when the input is empty', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    userEvent.clear(screen.getByRole('textbox'))

    expect(screen.getByRole('textbox')).toBeInvalid()

    userEvent.tab()

    expect(screen.getByText('Field is required')).toBeInTheDocument()
  })

  it('should disable the input when toggle is off', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    const toggle = screen.getByRole('switch')

    userEvent.click(toggle)

    expect(toggle).not.toBeChecked()
    expect(screen.getByRole('textbox')).toBeDisabled()
  })
})
