import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { PaymentMethodProblemChip } from './PaymentMethodProblemChip'

test('calls openCustomerPortal when clicked', () => {
  const openCustomerPortal = jest.fn()

  global.j = { openCustomerPortal }

  render(<PaymentMethodProblemChip />)

  userEvent.click(screen.getByText('Payment Method Problem').closest('button'))

  const openCustomerPortalCalls = window.j.openCustomerPortal.mock.calls

  expect(openCustomerPortalCalls.length).toEqual(1)
  expect(openCustomerPortalCalls[0][0]).toEqual('ADD_PAYMENT_SOURCE')

  delete global.j
})
