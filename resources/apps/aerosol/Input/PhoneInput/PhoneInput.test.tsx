import { act, render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { PhoneInput } from './PhoneInput'

let mockCountry: string
let mockPhoneNumber: string
let mockOnChange: () => void

describe('<PhoneInput/>', () => {
  beforeEach(() => {
    mockCountry = 'Andorra'
    mockPhoneNumber = '213134'
    mockOnChange = jest.fn()
  })

  const renderScreen = () =>
    render(
      <PhoneInput
        dropdownValue=''
        inputValue=''
        name='phone'
        country={mockCountry}
        phoneNumber={mockPhoneNumber}
        onChange={mockOnChange}
      />
    )

  it('should render a input for type telephone', () => {
    renderScreen()

    expect(screen.getByRole('textbox')).toHaveAttribute('type', 'tel')
  })

  it('should default to Canada if no country has been provided', () => {
    mockCountry = ''
    renderScreen()

    expect(screen.getByRole('button')).toHaveTextContent('+1')
  })

  it('should prefill the country code and phone number from the prop values that includes country name', () => {
    mockCountry = 'Bahamas'
    mockPhoneNumber = '12345456'

    renderScreen()

    expect(screen.getByRole('button')).toHaveTextContent('+1242')
    expect(screen.getByRole('textbox')).toHaveValue(mockPhoneNumber)
  })

  it('should prefill the country code and phone number from the prop values that includes country code', () => {
    mockCountry = 'BS'
    mockPhoneNumber = '12345456'

    renderScreen()

    expect(screen.getByRole('button')).toHaveTextContent('+1242')
    expect(screen.getByRole('textbox')).toHaveValue(mockPhoneNumber)
  })

  it('should not render the dialCode in the input value ', () => {
    const dialCode = '+1242'
    const phoneNumber = '12345456'

    mockPhoneNumber = dialCode.concat(phoneNumber)
    mockCountry = 'Bahamas'

    renderScreen()

    expect(screen.getByRole('textbox')).not.toHaveValue(mockPhoneNumber)
    expect(screen.getByRole('textbox')).toHaveValue(phoneNumber)
  })

  it('should update the dialCode of the phone number on country change', async () => {
    renderScreen()

    expect(screen.getByRole('button')).toHaveTextContent('+376')

    userEvent.click(screen.getByRole('button'))

    expect(screen.getByRole('menu')).toBeInTheDocument()

    await act(async () => userEvent.click(screen.getByRole('option', { name: '🇬🇧 United Kingdom +44' })))

    expect(screen.getByRole('button')).toHaveTextContent('+44')
  })
})
