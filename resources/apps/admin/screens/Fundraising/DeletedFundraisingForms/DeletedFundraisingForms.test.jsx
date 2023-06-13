import userEvent from '@testing-library/user-event'
import { render, screen, waitFor } from '@/mocks/setup'
import { mockFundraisingForm, mockFundraisingFormStats } from '@/mocks/data'
import { DeletedFundraisingForms } from './DeletedFundraisingForms'

describe('<DeletedFundraisingForms />', () => {
  const mockComponent = () => render(<DeletedFundraisingForms />)

  describe('when there are no deleted forms', () => {
    it('should render a link back to fundraising', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      const forms = screen.queryAllByTestId('deleted-form-panel')
      expect(forms).toHaveLength(0)

      expect(screen.queryByRole('link', { name: 'Go to fundraising experiences' })).toBeInTheDocument()
    })
  })

  describe('when there are deleted forms', () => {
    beforeEach(async () => {
      const { renderScreen, waitForLoadingToBeFinished, setFundraisingForms } = mockComponent()

      setFundraisingForms([
        mockFundraisingForm({
          id: '1',
          name: 'test donation form 1',
          ...mockFundraisingFormStats(),
        }),
        mockFundraisingForm({
          id: '2',
          name: 'test donation form 2',
          ...mockFundraisingFormStats(),
        }),
        mockFundraisingForm({
          id: '3',
          name: 'test donation form 3',
          ...mockFundraisingFormStats(),
        }),
      ])

      renderScreen()

      await waitForLoadingToBeFinished()
    })

    it('should render deleted forms', async () => {
      expect(screen.queryAllByTestId('deleted-form-panel')).toHaveLength(3)
    })

    it('should remove a form once it has been restored', async () => {
      expect(screen.getAllByTestId('deleted-form-panel')).toHaveLength(3)
      expect(screen.queryByText('test donation form 1')).toBeInTheDocument()

      userEvent.click(screen.getAllByRole('button', { name: /restore/i })[0])

      await waitFor(() => expect(screen.queryByText('test donation form 1')).not.toBeInTheDocument())

      expect(screen.getAllByTestId('deleted-form-panel')).toHaveLength(2)
    })
  })
})
