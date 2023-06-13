import { EmailOptinCard } from './EmailOptinCard'
import { screen, render } from '@/mocks/setup'
import userEvent from '@testing-library/user-event'

describe('<EmailOptinCard />', () => {
  const mockComponent = () => render(<EmailOptinCard />)

  describe('toggle enabled', () => {
    it('should enable the input', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      expect(screen.getByRole('switch')).toBeChecked()

      const descriptionInput = screen.getByRole('textbox')

      expect(descriptionInput).toBeEnabled()
      expect(descriptionInput).toBeValid()
    })

    it('should display and error when the input is empty', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      const descriptionInput = screen.getByRole('textbox')

      userEvent.clear(descriptionInput)
      userEvent.tab()

      expect(descriptionInput).toBeInvalid()
      expect(screen.getByText('Field is required')).toBeInTheDocument()
    })
  })

  describe('toggle is off', () => {
    it('should disable the input', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      const toggle = screen.getByRole('switch')

      userEvent.click(toggle)

      expect(toggle).not.toBeChecked()
      expect(screen.getByRole('textbox')).not.toBeEnabled()
    })
  })
})
