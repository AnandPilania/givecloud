import userEvent from '@testing-library/user-event'
import { screen, render, waitFor } from '@/mocks/setup'
import { mockFundraisingSettings } from '@/mocks/data'
import { FundraisingSettingsDialog } from './FundraisingSettingsDialog'

describe('<FundraisingSettingsDialog />', () => {
  let mockIsOpen
  let mockOnClose

  beforeEach(() => {
    mockIsOpen = true
    mockOnClose = jest.fn()
  })

  const mockComponent = () => render(<FundraisingSettingsDialog isOpen={mockIsOpen} onClose={mockOnClose} />)

  it('should render the Fundraising Settings header', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByRole('heading', { name: 'Fundraising Settings' })).toBeInTheDocument()
  })

  it('should prefill input values for fundraising settings', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()
    const mockFundraisingSettingsData = mockFundraisingSettings()

    renderScreen()

    await waitForLoadingToBeFinished()

    const firstWayToDonateLabel = screen.getAllByRole('textbox', { name: /label/i })[0]
    const firstWayToDonateLink = screen.getAllByRole('textbox', { name: /link/i })[0]

    expect(screen.getByRole('textbox', { name: 'Contact Email' })).toHaveValue(
      mockFundraisingSettingsData.orgSupportEmail
    )

    expect(screen.getByRole('textbox', { name: 'Contact Phone Number' })).toHaveValue(
      mockFundraisingSettingsData.orgSupportNumber
    )

    expect(firstWayToDonateLabel).toHaveValue(mockFundraisingSettingsData.orgOtherWaysToDonate[0].label)
    expect(firstWayToDonateLink).toHaveValue(mockFundraisingSettingsData.orgOtherWaysToDonate[0].link)
    expect(screen.getByRole('textbox', { name: /question/i })).toHaveValue(
      mockFundraisingSettingsData.orgFaqAlternativeQuestion
    )
    expect(screen.getByRole('textbox', { name: /answer/i })).toHaveValue(
      mockFundraisingSettingsData.orgFaqAlternativeAnswer
    )
    expect(screen.getByRole('textbox', { name: 'Mailing Address for Checks' })).toHaveValue(
      mockFundraisingSettingsData.orgCheckMailingAddress
    )
    expect(screen.getByRole('textbox', { name: 'Privacy Link' })).toHaveValue(
      mockFundraisingSettingsData.orgPrivacyPolicyUrl
    )
    expect(screen.getByRole('textbox', { name: 'Privacy Contact' })).toHaveValue(
      mockFundraisingSettingsData.orgPrivacyOfficerEmail
    )
  })

  it('should update Fundraising Settings values', async () => {
    const { renderScreen, waitForLoadingToBeFinished, setPatchFundraisingSettings } = mockComponent()
    const orgSupportEmail = 'support@org.com'

    setPatchFundraisingSettings({
      orgSupportEmail,
    })

    renderScreen()

    await waitForLoadingToBeFinished()

    const contactEmailInput = screen.getByRole('textbox', { name: 'Contact Email' })

    userEvent.clear(contactEmailInput)
    userEvent.type(contactEmailInput, orgSupportEmail)

    userEvent.click(screen.getByRole('button', { name: 'Save fundraising settings' }))

    const toast = await screen.findByRole('alert')

    expect(toast).toBeInTheDocument()
    expect(toast).toHaveTextContent('Fundraising settings updated!')
    expect(contactEmailInput).toHaveValue(orgSupportEmail)
  })

  it('should cancel any changes to fundraising settings when the close button is clicked', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()
    const { orgSupportEmail } = mockFundraisingSettings()

    renderScreen()

    await waitForLoadingToBeFinished()

    const contactEmailInput = screen.getByRole('textbox', { name: 'Contact Email' })

    userEvent.clear(contactEmailInput)
    userEvent.type(contactEmailInput, 'zipadee@dooda.ca')

    userEvent.click(screen.getByRole('button', { name: 'Close' }))

    await waitFor(() => {
      expect(contactEmailInput).toHaveValue(orgSupportEmail)
    })
  })
})
