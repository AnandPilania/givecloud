import { render } from '@testing-library/react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faHome } from '@fortawesome/pro-regular-svg-icons'
import { Icon } from './Icon'

jest.mock('@fortawesome/react-fontawesome', () => ({ FontAwesomeIcon: jest.fn(() => null) }))

afterEach(() => {
  jest.clearAllMocks()
})

test('renders a FontAwesomeIcon with expected props', () => {
  const className = 'icon'
  const icon = 'home'

  render(<Icon className={className} icon={icon} isFixedWidth spin />)

  const fontAwesomeIconInstances = FontAwesomeIcon.mock.calls[0]
  const props = fontAwesomeIconInstances[0]

  expect(props).toMatchObject({
    className,
    icon: faHome,
    fixedWidth: true,
    spin: true,
    'aria-label': icon,
  })
})
