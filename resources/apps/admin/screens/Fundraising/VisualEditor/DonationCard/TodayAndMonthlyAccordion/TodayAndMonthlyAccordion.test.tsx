import { render, screen } from '@/mocks/setup'
import userEvent from '@testing-library/user-event'
import { TodayAndMonthlyAccordion } from './TodayAndMonthlyAccordion'

describe('<TodayAndMonthlyAccordion />', () => {
  let mockIsOpen: boolean
  let mockSetIsOpen: () => void

  beforeEach(() => {
    mockIsOpen = true
    mockSetIsOpen = jest.fn()
  })

  const mockComponent = () => render(<TodayAndMonthlyAccordion isOpen={mockIsOpen} setIsOpen={mockSetIsOpen} />)

  it('should show an Alert when Today as Default is checked', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    const todayAsDefault = screen.getAllByRole('radio')[1]

    expect(todayAsDefault).not.toBeChecked()
    expect(screen.queryByRole('alert')).not.toBeInTheDocument()

    userEvent.click(todayAsDefault)

    expect(todayAsDefault).toBeChecked()
    expect(screen.queryByRole('alert')).toBeInTheDocument()
  })

  it('should not show an Alert when all other options are checked', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

    renderScreen()

    await waitForLoadingToBeFinished()

    const [monthlyAsDefault, ignoredTodayAsDefault, todayOnly, monthlyOnly] = screen.getAllByRole('radio')

    expect(monthlyAsDefault).toBeChecked()
    expect(screen.queryByRole('alert')).not.toBeInTheDocument()

    userEvent.click(todayOnly)
    expect(todayOnly).toBeChecked()
    expect(screen.queryByRole('alert')).not.toBeInTheDocument()

    userEvent.click(monthlyOnly)
    expect(monthlyOnly).toBeChecked()
    expect(screen.queryByRole('alert')).not.toBeInTheDocument()
  })
})
