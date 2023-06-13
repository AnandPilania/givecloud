import type { ChangeEvent } from 'react'
import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { CommandInput, SelectedType } from './CommandInput'
import { CommandInputOption } from './CommandInputOption'

interface Person {
  id: string
  name: string
}

const people = [
  { id: '1', name: 'Wade Cooper' },
  { id: '2', name: 'Arlene Mccoy' },
  { id: '3', name: 'Devon Webb' },
  { id: '4', name: 'Tom Cook' },
  { id: '5', name: 'Tanya Fox' },
  { id: '6', name: 'Hellen Schmidt' },
]
describe('<CommandInput/>', () => {
  let mockIsQueryEmpty: boolean
  let mockQuery: string
  let mockRenderValue: (item: SelectedType<Person>) => string
  let mockSelected: SelectedType<Person>
  let mockSetSelected: (value: SelectedType<Person>) => void
  let mockOnChange: ((value: string) => void) & ((event: ChangeEvent<HTMLInputElement>) => void)

  const displayValue = (value: SelectedType<Person>) => {
    if (typeof value === 'object') return value?.name
    return value ?? ''
  }

  beforeEach(() => {
    mockIsQueryEmpty = false
    mockQuery = ''
    mockRenderValue = jest.fn()
    mockSelected = ''
    mockSetSelected = jest.fn()
    mockOnChange = jest.fn()
  })

  const renderScreen = () => {
    const filteredPeople =
      mockQuery === ''
        ? people
        : people.filter((person) =>
            person.name.toLowerCase().replace(/\s+/g, '').includes(mockQuery.toLowerCase().replace(/\s+/g, ''))
          )

    return render(
      <CommandInput<Person>
        displayValue={displayValue}
        isQueryEmpty={mockIsQueryEmpty}
        query={mockQuery}
        selected={mockSelected}
        setSelected={mockSetSelected}
        onChange={mockOnChange}
        name='people'
      >
        {filteredPeople.map((people) => (
          <CommandInputOption key={people.id} value={people}>
            {people.name}
          </CommandInputOption>
        ))}
      </CommandInput>
    )
  }

  it('should render an input with no value', () => {
    renderScreen()

    const input = screen.getByRole('combobox')

    expect(input).toBeInTheDocument()
    expect(input).toHaveValue('')
  })

  it('should render the listbox when the user types a value', () => {
    renderScreen()

    const input = screen.getByRole('combobox')

    userEvent.type(input, 'wa')

    expect(screen.getByRole('listbox')).toBeInTheDocument()
  })

  it('should set the selected value on click when user selects option within listbox and close the listbox', () => {
    renderScreen()

    const input = screen.getByRole('combobox')

    userEvent.type(input, 'wa')

    const selectedOption = screen.getByRole('option', { name: people[0].name })
    userEvent.click(selectedOption)

    waitFor(() => {
      expect(input).toHaveDisplayValue(people[0].name)
      expect(screen.queryByRole('listbox')).not.toBeInTheDocument()
    })
  })
})
