import { render, screen } from '@testing-library/react'
import { ExternalLinkIcon } from '@/components/ExternalLinkIcon'
import { faExternalLink } from '@fortawesome/pro-regular-svg-icons'

test('renders expected icon', () => {
  render(<ExternalLinkIcon />)

  expect(screen.getByTitle(faExternalLink.iconName)).toBeInTheDocument()
})
