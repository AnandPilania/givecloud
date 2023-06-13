import { render, screen, waitFor } from '@/mocks/setup'
import userEvent from '@testing-library/user-event'
import { OrgPanel } from './OrgPanel'
import { mockOrgSettings } from '@/mocks/data'

jest.mock('react-router', () => ({
  ...jest.requireActual('react-router'),
  useLocation: jest.fn(),
  useHistory: jest.fn(),
}))

describe('<OrgPanel/>', () => {
  const mockedValues = [
    {
      mocked: 'English',
      empty: 'No language selected',
    },
    {
      mocked: '1 road way',
      empty: 'No address provided',
    },
    {
      mocked: 'Museums, Zoos & Aquariums',
      empty: 'No market category',
    },
    {
      mocked: 'www.puppy.com',
      empty: 'No Organization Site',
    },
    {
      mocked: '40',
      empty: 'No fundraising goal',
    },
  ]
  const mockComponent = () => render(<OrgPanel />)

  it.each(mockedValues)('Should render the organization details %o', async ({ mocked }) => {
    const { renderScreen, setOrgSettings, waitForLoadingToBeFinished } = mockComponent()

    const mockOrgSettingsData = mockOrgSettings({
      locale: 'English',
      orgLegalAddress: '1 road way',
      marketCategory: 'Museums, Zoos & Aquariums',
      orgWebsite: 'www.puppy.com',
      annualFundraisingGoal: '40',
    })

    setOrgSettings(mockOrgSettingsData)

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByText(mocked)).toBeInTheDocument()
  })

  it.each(mockedValues)('should render default value text %s when the value is empty', async ({ empty }) => {
    const { renderScreen, setOrgSettings, waitForLoadingToBeFinished } = mockComponent()

    setOrgSettings({
      locale: '',
      orgLegalAddress: '',
      marketCategory: '',
      annualFundraisingGoal: '',
      orgWebsite: '',
    })

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByText(empty)).toBeInTheDocument()
  })

  it('should open the org settings dialog when the user clicks on the edit button', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    userEvent.click(screen.getByRole('link', { name: 'edit organization settings' }))

    await waitFor(() => expect(screen.getByTestId('org-settings-form')).toBeInTheDocument())
  })

  it('Button CTA should render add charity number when org legal number is missing', async () => {
    const { renderScreen, waitForLoadingToBeFinished, setOrgSettings } = mockComponent()

    setOrgSettings({
      orgLegalNumber: '',
    })

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByRole('link', { name: 'add charity number' })).toBeInTheDocument()
  })
})
