import { render, screen, waitFor } from '@/mocks/setup'
import { FundraisingForms } from './FundraisingForms'
import { useFundraisingFormsQuery } from './useFundraisingFormsQuery'

let mockSearch = ''

beforeEach(() => {
  mockSearch = ''
})

jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  useLocation: jest.fn(() => ({ pathname: '', search: mockSearch })),
}))

describe('<FundraisingForms/>', () => {
  const mockComponent = (options = {}) => render(<FundraisingForms />, { ...options })

  it('should render all fundraising forms collected from the backend', async () => {
    const { mockQueryResult, renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    const { result } = mockQueryResult(useFundraisingFormsQuery)
    const forms = screen.getAllByTestId('fundraising-form-panel')

    expect(result.current.data).toHaveLength(forms.length)
  })

  it('should by default open the visual editor if the url contains a createFundraisingForm param', async () => {
    const { renderScreen } = mockComponent()

    mockSearch = 'createFundraisingForm'

    renderScreen()

    await waitFor(() => expect(screen.getByTestId('fundraising-form')).toBeInTheDocument())
  })

  it('should render the first form as default', async () => {
    //@ts-ignore
    const { setFundraisingForms, renderScreen, waitForLoadingToBeFinished } = mockComponent()

    setFundraisingForms({
      isDefaultForm: true,
    })

    renderScreen()

    await waitForLoadingToBeFinished()

    const forms = screen.getAllByTestId('fundraising-form-panel')

    expect(forms[0]).toHaveTextContent(/default experience/i)
    expect(forms[1]).not.toHaveTextContent(/default experience/i)
    expect(forms[2]).not.toHaveTextContent(/default experience/i)
  })
})
