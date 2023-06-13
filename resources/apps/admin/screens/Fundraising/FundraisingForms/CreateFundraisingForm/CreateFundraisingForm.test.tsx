import { render, screen, waitFor } from '@/mocks/setup'
import userEvent from '@testing-library/user-event'
import { CreateFundraisingForm } from './CreateFundraisingForm'

describe('<CreateFundraisingForm/>', () => {
  const mockComponent = () => render(<CreateFundraisingForm isOpen />)

  it('should render a fundraising form', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    await waitFor(() => expect(screen.getByTestId('fundraising-form')).toBeInTheDocument())
  })

  it('should throw an error if the user submits a form without a name', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    const input = screen.getByRole('textbox', { name: 'Experience Name' })

    expect(input).toHaveValue('')

    userEvent.click(screen.getByRole('button', { name: 'Save' }))

    await waitFor(() => expect(screen.getByTestId('toast-error')).toHaveTextContent('Error in Template & Branding'))
  })

  it('should submit the form if the user inputs a name and clicks on save', async () => {
    //@ts-ignore
    const { renderScreen, waitForLoadingToBeFinished, setPostFundraisingForm } = mockComponent()
    const name = 'test donation form 1'

    setPostFundraisingForm({ name })

    renderScreen()

    await waitForLoadingToBeFinished()

    const input = screen.getByRole('textbox', { name: 'Experience Name' })

    userEvent.clear(input)
    userEvent.type(input, name)
    userEvent.tab()
    userEvent.click(screen.getByRole('button', { name: 'Save' }))

    await waitFor(() => expect(screen.getByTestId('toast-success')).toHaveTextContent(`${name} Created!`))
  })
})
