import { render, screen } from '@/mocks/setup'
import userEvent from '@testing-library/user-event'
import { FundraisingFormDashboard } from './FundraisingFormDashboard'

let mockSearch: string
let mockpathName: string
let mockId: string

jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  useLocation: jest.fn(() => ({ search: mockSearch, pathname: mockpathName })),
  useParams: jest.fn(() => ({ id: mockId })),
}))

jest.mock('react-chartjs-2', () => {
  const { forwardRef } = jest.requireActual('react')
  return {
    Line: forwardRef(() => null),
  }
})

describe('<FundraisingFormDashboard/>', () => {
  const mockCustomDPFields = () =>
    Array(23)
      .fill({ key: '', label: 'mockMeta', field: 'META', default: 'mock', autocomplete: 'false' })
      .map((field, index) => (field.key = `dp_meta${index + 9}`))

  const mockComponent = () =>
    render(<FundraisingFormDashboard />, { config: { donorPerfectConfig: { udfs: mockCustomDPFields() } } })

  beforeEach(() => {
    mockSearch = ''
    mockpathName = ''
    mockId = '1'
  })

  it('should render the name of the Form', async () => {
    //@ts-ignore
    const { renderScreen, setFundraisingForm, waitForLoadingToBeFinished } = mockComponent()

    const name = 'My New Form'

    setFundraisingForm({
      name,
    })

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByText(name)).toBeInTheDocument()
  })

  it('should indicate if the form is the default Form', async () => {
    //@ts-ignore
    const { renderScreen, setFundraisingForm, waitForLoadingToBeFinished } = mockComponent()

    const isDefaultForm = true

    setFundraisingForm({
      isDefaultForm,
    })

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByText(/Default Experience/i)).toBeInTheDocument()
  })

  it('should not indicate the default form if the form is not the default form', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.queryByText(/Default Experience/i)).not.toBeInTheDocument()
  })

  it('should open the Visual Editor when updateFundraisingForm is in the url ', async () => {
    mockSearch = 'updateFundraisingForm'

    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByTestId('fundraising-form')).toBeInTheDocument()
  })

  it('should open the embed dialog when embedFundraisingForm is in the url', async () => {
    mockSearch = 'embedFundraisingForm'

    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByTestId('embed-intro')).toBeInTheDocument()
  })

  it('should open the delete dialog when deleteFundraisingForm is in the url', async () => {
    mockSearch = 'deleteFundraisingForm'

    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getByRole('heading', { name: `Delete test donation form 1` })).toBeInTheDocument()
  })

  it('should open the integrations dialog when integrations is in the url', async () => {
    mockSearch = 'integrations'

    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    expect(screen.getAllByRole('heading', { name: 'Integrations' })).toHaveLength(2)
  })

  it('should copy the short url when the share panel is clicked', async () => {
    let shortlinkUrl = ''

    Object.assign(navigator, {
      clipboard: {
        writeText: jest.fn((text) => {
          shortlinkUrl = text
        }),
        readText: jest.fn(() => shortlinkUrl),
      },
    })

    //@ts-ignore
    const { renderScreen, waitForLoadingToBeFinished, setFundraisingForm } = mockComponent()

    shortlinkUrl = 'www.yahoo.com'

    setFundraisingForm({
      shortlinkUrl: shortlinkUrl,
    })

    renderScreen()

    await waitForLoadingToBeFinished()

    const sharePanel = screen.getByRole('button', {
      hidden: true,
      name: `Share Your Link`,
    })

    userEvent.click(sharePanel)

    expect(navigator.clipboard.writeText).toBeCalledTimes(1)
    expect(navigator.clipboard.writeText).toHaveBeenCalledWith(shortlinkUrl)
  })
})
