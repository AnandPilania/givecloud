import { TEXT_TYPES } from '@/shared/constants/theme'
import { render, screen } from '@testing-library/react'
import { Text } from './Text'

describe('<Text/>', () => {
  let mockIsMarginless
  let mockIsTruncated
  let mockIsBold
  let mockType
  let textValue
  beforeEach(() => {
    mockIsMarginless = false
    mockIsTruncated = false
    mockIsBold = false
    mockType = 'h1'
    textValue = 'If you like pina coladas'
  })

  const renderScreen = () =>
    render(
      <Text isMarginless={mockIsMarginless} isTruncated={mockIsTruncated} isBold={mockIsBold} type={mockType}>
        {textValue}
      </Text>
    )

  it.each(TEXT_TYPES)('should create the correct HTML %s element when the prop type has the prop value', (type) => {
    const element = type === 'footnote' ? 'p' : type
    mockType = type

    renderScreen()

    const { tagName } = screen.getByText(textValue)

    expect(tagName.toLowerCase()).toBe(element)
  })

  it('should render bold text element when the isBold prop is true', () => {
    mockIsBold = true

    renderScreen()

    expect(screen.getByRole('heading', { name: textValue })).toHaveClass('bold')
  })

  it('should truncate text element when the isTruncated prop is true', () => {
    mockIsTruncated = true

    renderScreen()

    expect(screen.getByRole('heading', { name: textValue })).toHaveClass('truncate')
  })

  it('should not contain margin when the isMarginless prop is true', () => {
    mockIsMarginless = true

    renderScreen()

    expect(screen.getByRole('heading', { name: textValue })).toHaveClass('noMargin')
  })
})
