import { screen, render } from '@/mocks/setup'
import { EmployerMatchingCard } from './EmployerMatchingCard'

describe('<EmployerMatchingCard />', () => {
  const mockComponent = () => render(<EmployerMatchingCard />)

  describe('when the double the donation integration is not enabled', () => {
    it('should disable the toggle', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      expect(screen.getByRole('switch')).toBeDisabled()
    })

    it('should show a link to enable the integration', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      expect(
        screen.getByRole('link', { name: 'Enable the Double the Donation 360 Match integration.' })
      ).toBeInTheDocument()
    })
  })
})
