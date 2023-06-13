import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import mockGivecloud from '@/mocks/givecloud'
import { CurrencySelector } from './CurrencySelector'

const mockAllCurrencies = mockGivecloud.config.currencies

describe('<CurrencySelector />', () => {
  const mockOnChange = jest.fn()

  const mockCurrentCurrency = {
    code: 'HKD',
    name: 'Hong Kong Dollar',
  }

  const renderComponent = () =>
    render(<CurrencySelector currencyCode={mockCurrentCurrency.code} onChange={mockOnChange} />)

  it('should render with a default local currency', () => {
    renderComponent()

    expect(screen.getByText(`${mockCurrentCurrency.code}`)).toBeInTheDocument()
  })

  it('should list top currencies and a default local currency', () => {
    renderComponent()

    userEvent.click(screen.getByRole('button', { name: `Selected currency: ${mockCurrentCurrency.name}` }))

    expect(screen.getAllByText(mockCurrentCurrency.code, { exact: false })).not.toBeNull()
    expect(screen.getAllByText(mockAllCurrencies[0].code, { exact: false })).not.toBeNull()
    expect(screen.getAllByText(mockAllCurrencies[1].code, { exact: false })).not.toBeNull()
  })
})
