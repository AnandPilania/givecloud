import { render, screen, waitFor } from '@/mocks/setup'
import userEvent from '@testing-library/user-event'
import { mockOrgSettings } from '@/mocks/data'
import { OrgSettingsDialog } from './OrgSettingsDialog'

describe('<OrgSettingsDialog/>', () => {
  let mockIsopen
  let mockIsClose

  beforeEach(() => {
    mockIsopen = true
    mockIsClose = jest.fn()
  })

  const mockComponent = (options = {}) =>
    render(<OrgSettingsDialog isOpen={mockIsopen} onClose={mockIsClose} />, { ...options })

  it('should render the org setting header', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByRole('heading', { name: 'Organization Settings' })).toBeInTheDocument()
  })

  it('should prefill the inputs with values from org settings', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    const mockOrgSettingsData = mockOrgSettings()

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByRole('textbox', { name: 'Legal Name' })).toHaveValue(mockOrgSettingsData.orgLegalName)
    expect(screen.getByRole('textbox', { name: 'Legal Address' })).toHaveValue(mockOrgSettingsData.orgLegalAddress)
    expect(screen.getByRole('textbox', { name: 'Website' })).toHaveValue(mockOrgSettingsData.orgWebsite)
    expect(screen.getByRole('textbox', { name: 'Registered Country' })).toHaveValue(mockOrgSettingsData.orgLegalCountry)
    expect(screen.getByRole('textbox', { name: 'Charity Number' })).toHaveValue(mockOrgSettingsData.orgLegalNumber)
    expect(screen.getByRole('textbox', { name: 'Website' })).toHaveValue(mockOrgSettingsData.orgWebsite)
  })

  it('should be able to update the values of the org settings', async () => {
    const { renderScreen, waitForLoadingToBeFinished, setPatchOrgSettings } = mockComponent()
    const orgLegalName = 'Puppies loves pickles'

    setPatchOrgSettings({
      orgLegalName,
    })

    renderScreen()

    await waitForLoadingToBeFinished()

    const legalNameInput = screen.getByRole('textbox', { name: 'Legal Name' })

    userEvent.clear(legalNameInput)
    userEvent.type(legalNameInput, orgLegalName)
    userEvent.click(screen.getByRole('button', { name: 'Save organization settings' }))

    await waitFor(() => {
      expect(screen.getByText('Organization settings updated!')).toBeInTheDocument()
      expect(legalNameInput).toHaveValue(orgLegalName)
    })
  })

  it('should cancel any updates made to the form on click of the close button', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()
    const { orgLegalName } = mockOrgSettings()

    renderScreen()

    await waitForLoadingToBeFinished()

    const legalNameInput = screen.getByRole('textbox', { name: 'Legal Name' })

    userEvent.clear(legalNameInput)
    userEvent.type(legalNameInput, 'new fancy name')
    userEvent.click(screen.getByRole('button', { name: 'Close' }))

    await waitFor(() => {
      expect(legalNameInput).toHaveValue(orgLegalName)
    })
  })

  it('should throw an error if the Charity number is missing', async () => {
    const { renderScreen, waitForLoadingToBeFinished, setOrgSettings } = mockComponent()

    setOrgSettings({
      orgLegalNumber: '',
    })

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByTestId('error-0')).toHaveTextContent('Charity number missing')
  })

  it('should render a readonly registered country input if not a superuser', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    const readonlyinput = screen.getByRole('textbox', { name: 'Registered Country' })

    expect(readonlyinput).toHaveAttribute('readonly')
    expect(readonlyinput).toHaveDisplayValue('NO')
  })

  it('should surface a registered country command input if a superuser', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent({ config: { isSuperUser: true } })

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.queryByRole('textbox', { name: 'Registered Country' })).not.toBeInTheDocument()
    expect(screen.getByTestId('legal-country-command-input')).toBeInTheDocument()
  })
})
