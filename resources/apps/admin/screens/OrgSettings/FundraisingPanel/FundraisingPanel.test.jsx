import { screen, render } from '@/mocks/setup'
import { mockFundraisingSettings } from '@/mocks/data'
import { FundraisingPanel } from './FundraisingPanel'

jest.mock('react-router', () => ({
  ...jest.requireActual('react-router'),
  useLocation: jest.fn(),
  useHistory: jest.fn(),
}))

describe('<FundraisingPanel />', () => {
  const mockComponent = () => render(<FundraisingPanel />)

  const mockedValues = [
    {
      mocked: '867-5309',
      empty: 'No Contact Phone Number',
    },
    {
      mocked: 'google@google.com',
      empty: 'No Contact Email',
    },
    {
      mocked: 'How can I donate?',
      empty: 'No Alternative Question',
    },
    {
      mocked: 'Whenever wherever',
      empty: 'No Alternative Answer',
    },
    {
      mocked: 'Beverly Hills 90210',
      empty: 'No Mailing Address for Checks',
    },
    {
      mocked: 'privacyoff@org.ca',
      empty: 'No Privacy Contact',
    },
    {
      mocked: 'privacyinfo@org.ca',
      empty: 'No Privacy Link',
    },
  ]

  it.each(mockedValues)('should render fundraising setting details for: %o', async ({ mocked }) => {
    const { renderScreen, setFundraisingSettings, waitForLoadingToBeFinished } = mockComponent()

    const mockFundraisingSettingsData = mockFundraisingSettings({
      orgSupportNumber: '867-5309',
      orgSupportEmail: 'google@google.com',
      orgFaqAlternativeQuestion: 'How can I donate?',
      orgFaqAlternativeAnswer: 'Whenever wherever',
      orgCheckMailingAddress: 'Beverly Hills 90210',
      orgPrivacyOfficerEmail: 'privacyoff@org.ca',
      orgPrivacyPolicyUrl: 'privacyinfo@org.ca',
    })

    setFundraisingSettings(mockFundraisingSettingsData)

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByText(mocked)).toBeInTheDocument()
  })

  it('should render fundraising details for other ways to donate', async () => {
    const { renderScreen, setFundraisingSettings, waitForLoadingToBeFinished } = mockComponent()

    const mockOtherWaysToDonate = mockFundraisingSettings({
      orgOtherWaysToDonate: [{ id: 1, label: 'spaghetti', href: 'meatballs.org' }],
    })

    setFundraisingSettings(mockOtherWaysToDonate)

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByText('spaghetti')).toBeInTheDocument()
    expect(screen.getByText('meatballs.org')).toBeInTheDocument()
  })

  it.each(mockedValues)('should render default text %s when fundraising detail value is empty', async ({ empty }) => {
    const { renderScreen, setFundraisingSettings, waitForLoadingToBeFinished } = mockComponent()

    const mockFundraisingSettingsData = mockFundraisingSettings({
      orgSupportNumber: '',
      orgSupportEmail: '',
      orgFaqAlternativeQuestion: '',
      orgFaqAlternativeAnswer: '',
      orgCheckMailingAddress: '',
      orgPrivacyOfficerEmail: '',
      orgPrivacyPolicyUrl: '',
    })

    setFundraisingSettings(mockFundraisingSettingsData)

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByText(empty)).toBeInTheDocument()
  })

  it('should render default text when there are no other ways to donate', async () => {
    const { renderScreen, setFundraisingSettings, waitForLoadingToBeFinished } = mockComponent()

    const mockOtherWaysToDonate = mockFundraisingSettings({
      orgOtherWaysToDonate: [{ id: 1, label: '', href: '' }],
    })

    setFundraisingSettings(mockOtherWaysToDonate)

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByText('No Other Ways to Donate')).toBeInTheDocument()
  })

  it('should open the fundraising dialog when the edit button is clicked', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByRole('heading', { name: 'Fundraising' })).toBeInTheDocument()
  })
})
