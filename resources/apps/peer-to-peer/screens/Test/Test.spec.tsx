import { render, screen } from '@testing-library/react'
import { Test } from './Test'

describe('Test', () => {
  it('should work', () => {
    render(<Test />)
    expect(screen.getByTestId('test')).toBeInTheDocument()
  })
})
