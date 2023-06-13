import type { IconDefinition } from '@fortawesome/pro-regular-svg-icons'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Button } from './Button'
import { faCheck } from '@fortawesome/pro-regular-svg-icons'

describe('<Button />', () => {
  let mockIcon: IconDefinition | undefined
  const mockOnClick = jest.fn()

  const renderScreen = () =>
    render(
      <Button icon={mockIcon} onClick={mockOnClick}>
        Test Button
      </Button>
    )

  afterEach(() => {
    jest.clearAllMocks()
  })

  it('renders an Icon if provided an icon', () => {
    mockIcon = faCheck
    renderScreen()

    expect(screen.getByLabelText(faCheck.iconName)).toBeInTheDocument()
  })

  it('renders given children', () => {
    renderScreen()

    expect(screen.getByText('Test Button')).toBeInTheDocument()
  })

  it('should call onClick when triggered', () => {
    renderScreen()

    userEvent.click(screen.getByRole('button'))

    expect(mockOnClick.mock.calls).toHaveLength(1)
  })
})
