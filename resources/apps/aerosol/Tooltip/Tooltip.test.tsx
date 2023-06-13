import type { Props } from './Tooltip'
import { render, screen, act } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Tooltip } from './Tooltip'

describe('<Tooltip/>', () => {
  let mockIsHidden: Props['isHidden']
  let mockIsTriggeredOnClick: Props['isTriggeredOnClick']
  let mockTooltipContent: Props['tooltipContent']
  let mockTooltipText: string
  let mockPlacement: Props['placement']

  beforeEach(() => {
    mockIsHidden = false
    mockIsTriggeredOnClick = false
    mockTooltipText = 'das is eine kleine katze'
    mockTooltipContent = <span>{mockTooltipText}</span>
    mockPlacement = 'bottom'
  })

  const renderScreen = () =>
    render(
      <Tooltip tooltipContent={mockTooltipContent} isHidden={mockIsHidden} placement={mockPlacement}>
        <button>Ciao Bella</button>
      </Tooltip>
    )

  it('should render its children', () => {
    renderScreen()

    expect(screen.getByRole('button')).toBeInTheDocument()
  })

  it('should not render the tooltip content by default', () => {
    renderScreen()

    expect(screen.queryByText(mockTooltipText)).not.toBeInTheDocument()
  })

  it('should render tooltip content on hover', async () => {
    renderScreen()
    const tooltip = screen.getByRole('tooltip')

    expect(screen.queryByText(mockTooltipText)).not.toBeInTheDocument()

    await act(async () => {
      userEvent.hover(tooltip)
    })

    expect(screen.getByText(mockTooltipText)).toBeInTheDocument()
  })

  it('should not render the tooltip content on hover when isHidden prop is true', async () => {
    mockIsHidden = true
    renderScreen()

    const button = screen.getByRole('button')

    await act(async () => {
      userEvent.hover(button)
    })

    expect(screen.queryByText(mockTooltipText)).not.toBeInTheDocument()
  })

  it('should render tooltip content on click', async () => {
    mockIsTriggeredOnClick = true
    renderScreen()
    const tooltip = screen.getByRole('tooltip')

    expect(screen.queryByText(mockTooltipText)).not.toBeInTheDocument()

    await act(async () => {
      userEvent.click(tooltip)
    })

    expect(screen.getByText(mockTooltipText)).toBeInTheDocument()
  })
})
