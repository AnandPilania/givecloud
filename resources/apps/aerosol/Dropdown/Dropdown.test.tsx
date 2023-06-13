import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Dropdown } from './Dropdown'
import { DropdownHeader } from './DropdownHeader'
import { DropdownButton } from './DropdownButton'
import { DropdownItems } from './DropdownItems'
import { DropdownItem } from './DropdownItem'
import { DropdownDivider } from './DropdownDivider'
import { DropdownContent } from './DropdownContent'

describe('<Dropdown/>', () => {
  let mockValue: string
  let mockIsOpenByDefault: boolean
  const mockSetValue = jest.fn()

  beforeEach(() => {
    mockIsOpenByDefault = false
    mockValue = 'one'
  })

  const renderScreen = () =>
    render(
      <Dropdown isOpenByDefault={mockIsOpenByDefault} value={mockValue}>
        <DropdownContent>
          <DropdownButton>{mockValue}</DropdownButton>
          <DropdownItems>
            <DropdownHeader>Header</DropdownHeader>
            <DropdownItem value='one' onClick={mockSetValue}>
              one
            </DropdownItem>
            <DropdownItem value='two' onClick={mockSetValue}>
              two
            </DropdownItem>
            <DropdownItem value='three' onClick={mockSetValue}>
              three
            </DropdownItem>
            <DropdownDivider />
            <DropdownItem href='google' value='google'>
              go to google
            </DropdownItem>
          </DropdownItems>
        </DropdownContent>
      </Dropdown>
    )

  it('should render a dropdown button without dropdown content by default', () => {
    renderScreen()

    expect(screen.getByRole('button', { name: mockValue })).toBeInTheDocument()
    expect(screen.queryByRole('menu')).not.toBeInTheDocument()
  })

  it('should render with open menu if isOpenByDefault is true', () => {
    mockIsOpenByDefault = true

    renderScreen()

    expect(screen.getByRole('menu')).toBeInTheDocument()
  })

  it('should open the dropdown menu onClick of the button', () => {
    renderScreen()

    userEvent.click(screen.getByRole('button', { name: mockValue }))

    expect(screen.getByRole('menu')).toBeInTheDocument()
  })

  it('should render a selected aria role to the selected menu item', () => {
    mockIsOpenByDefault = true

    renderScreen()

    expect(screen.getByRole('option', { selected: true })).toHaveTextContent(mockValue)
  })
})
