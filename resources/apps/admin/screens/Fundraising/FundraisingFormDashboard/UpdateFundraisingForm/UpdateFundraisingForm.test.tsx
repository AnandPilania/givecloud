import { render, screen, waitFor } from '@/mocks/setup'
import userEvent from '@testing-library/user-event'
import { UpdateFundraisingForm } from './UpdateFundraisingForm'
import { useFundraisingFormQuery } from './useFundraisingFormQuery'

const id = '1'
jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  useParams: jest.fn(() => ({ id })),
}))

describe('<UpdateFundraisingForm />', () => {
  const mockComponent = () => render(<UpdateFundraisingForm isOpen />)

  it('should render a fundraising form', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    await waitFor(() => expect(screen.getByTestId('fundraising-form')).toBeInTheDocument())
  })

  it('should throw an error if the name field is edited and left empty when save is clicked', async () => {
    const { renderScreen, waitForLoadingToBeFinished, mockQueryResult } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    const { result } = mockQueryResult(() => useFundraisingFormQuery({ id }))

    await waitFor(() => expect(result.current.isSuccess).toBeTruthy())

    const nameInput = screen.getByRole('textbox', { name: 'Experience Name' })

    userEvent.clear(nameInput)

    expect(nameInput).toHaveValue('')

    userEvent.click(screen.getByRole('button', { name: 'Save' }))

    await waitFor(() => expect(screen.getByTestId('toast-error')).toHaveTextContent('Error in Template & Branding'))
  })

  it('should submit if the name field is updated with a new value and save is clicked', async () => {
    //@ts-ignore
    const { renderScreen, waitForLoadingToBeFinished, mockQueryResult, setPatchFundraisingForms } = mockComponent()
    const name = 'test donation form 1001'

    setPatchFundraisingForms({
      name,
    })

    renderScreen()

    await waitForLoadingToBeFinished()

    const { result } = mockQueryResult(() => useFundraisingFormQuery({ id }))

    await waitFor(() => expect(result.current.isSuccess).toBeTruthy())

    const nameInput = screen.getByRole('textbox', { name: 'Experience Name' })

    userEvent.clear(nameInput)
    userEvent.type(nameInput, name)
    userEvent.click(screen.getByRole('button', { name: 'Save' }))

    await waitFor(() => expect(screen.getByTestId('toast-success')).toHaveTextContent(`${name} Saved!`))
  })
})
