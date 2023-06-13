import userEvent from '@testing-library/user-event'
import { render, screen } from '@/mocks/setup'
import { TilesDefaultAmountAccordion } from './TilesDefaultAmountAccordion'

describe('<TilesDefaultAmountAccordion />', () => {
  let mockIsOpen: boolean
  let mockSetIsOpen: () => void

  beforeEach(() => {
    mockIsOpen = true
    mockSetIsOpen = jest.fn()
  })

  const mockComponent = () => render(<TilesDefaultAmountAccordion isOpen={mockIsOpen} setIsOpen={mockSetIsOpen} />)

  describe('when automatic is checked', () => {
    it('should not show custom tile amount inputs if automatic is checked', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      expect(screen.getByRole('radio', { name: /automatic/i })).toBeChecked()
      expect(screen.getByRole('radio', { name: /customize/i })).not.toBeChecked()
      expect(screen.queryAllByRole('textbox')).toHaveLength(0)
    })
  })

  describe('when customize is checked', () => {
    beforeEach(async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      userEvent.click(screen.getByRole('radio', { name: /customize/i }))
    })

    it('should show custom tile amount inputs if customize is checked', () => {
      expect(screen.queryAllByRole('textbox')).toHaveLength(6)
    })

    it('should show an error, if a custom amount is less than $5', () => {
      const customAmtInputs = screen.queryAllByRole('textbox')

      userEvent.clear(customAmtInputs[0])
      userEvent.click(customAmtInputs[1])

      expect(customAmtInputs[0]).toBeInvalid()
      expect(screen.getByTestId('error-0')).toBeInTheDocument()
    })

    it('should not show an error, if a custom amount is $5 or more', () => {
      const customAmtInputs = screen.queryAllByRole('textbox')

      userEvent.clear(customAmtInputs[0])
      userEvent.type(customAmtInputs[0], '$900')

      expect(customAmtInputs[0]).toBeValid()
      expect(screen.queryByTestId('error-0')).not.toBeInTheDocument()
    })
  })
})
