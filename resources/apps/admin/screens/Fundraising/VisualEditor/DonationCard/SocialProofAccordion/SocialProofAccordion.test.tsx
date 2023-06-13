import userEvent from '@testing-library/user-event'
import { render, screen } from '@/mocks/setup'
import { SocialProofAccordion } from './SocialProofAccordion'

describe('<SocialProofAccordion />', () => {
  let mockIsOpen: boolean
  let mockSetIsOpen: () => void

  beforeEach(() => {
    mockIsOpen = true
    mockSetIsOpen = jest.fn()
  })

  const mockComponent = () => render(<SocialProofAccordion isOpen={mockIsOpen} setIsOpen={mockSetIsOpen} />)

  it('should enable inputs when toggle is on', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByRole('switch')).toBeChecked()

    const socialProofOptions = screen.getAllByRole('radio')

    expect(socialProofOptions[0]).toBeEnabled()
    expect(socialProofOptions[1]).toBeEnabled()
  })

  it('should disable inputs when toggle is off', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    const toggle = screen.getByRole('switch')

    userEvent.click(toggle)

    expect(toggle).not.toBeChecked()

    const socialProofOptions = screen.getAllByRole('radio')

    expect(socialProofOptions[0]).toBeDisabled()
    expect(socialProofOptions[1]).toBeDisabled()
  })
})
