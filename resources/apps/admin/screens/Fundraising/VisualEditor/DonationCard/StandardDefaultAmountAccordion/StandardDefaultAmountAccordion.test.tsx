import { render, screen, fireEvent } from '@/mocks/setup'
import userEvent from '@testing-library/user-event'
import { StandardDefaultAmountAccordion } from './StandardDefaultAmountAccordion'

describe('<StandardDefaultAmountAccordion />', () => {
  let mockIsOpen: boolean
  let mockSetIsOpen: () => void

  beforeEach(() => {
    mockIsOpen = true
    mockSetIsOpen = jest.fn()
  })

  const mockComponent = () => render(<StandardDefaultAmountAccordion isOpen={mockIsOpen} setIsOpen={mockSetIsOpen} />)

  describe('when automatic is checked', () => {
    it('should not enable the custom amount input', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      expect(screen.getByRole('radio', { name: /automatic/i })).toBeChecked()
      expect(screen.getByRole('radio', { name: /custom/i })).not.toBeChecked()
      expect(screen.getByRole('textbox')).toBeDisabled()
    })
  })

  describe('when custom is checked', () => {
    it('should enable the custom amount input', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      userEvent.click(screen.getByText('Custom'))

      expect(screen.getByRole('radio', { name: /automatic/i })).not.toBeChecked()
      expect(screen.getByRole('radio', { name: /custom/i })).toBeChecked()
      expect(screen.getByRole('textbox')).toBeEnabled()
    })

    it('should show an error if the custom amount is less than $5', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      const customAmtInput = screen.getByRole('textbox', { name: 'Custom default amount' })

      fireEvent.change(customAmtInput, { target: { value: '3', name: 'customDefaultAmount' } })
      fireEvent.focusOut(customAmtInput)

      expect(screen.getByTestId('error-0')).toBeInTheDocument()
    })

    it('should not show an error if the custom amount is $5 or more', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      const customAmtInput = screen.getByRole('textbox', { name: 'Custom default amount' })

      fireEvent.change(customAmtInput, { target: { value: '6', name: 'customDefaultAmount' } })
      fireEvent.focusOut(customAmtInput)

      expect(screen.queryByTestId('error-0')).not.toBeInTheDocument()
    })
  })
})
