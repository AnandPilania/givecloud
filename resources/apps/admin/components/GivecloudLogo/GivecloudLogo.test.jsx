import { render, screen } from '@testing-library/react'
import { GivecloudLogo } from './GivecloudLogo'

test('renders default src when not withName', () => {
  render(<GivecloudLogo />)

  expect(screen.getByAltText('Givecloud')).toHaveAttribute(
    'src',
    'https://cdn.givecloud.co/static/etc/givecloud-logo-mark-full-color-rgb.svg'
  )
})

test('renders expected src when withName', () => {
  render(<GivecloudLogo withName />)

  expect(screen.getByAltText('Givecloud')).toHaveAttribute(
    'src',
    'https://cdn.givecloud.co/static/etc/givecloud-logo-full-color-rgb.svg'
  )
})
