import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { BillingWarningChip } from './BillingWarningChip'

test('calls openCustomerPortal when clicked', () => {
  const openCustomerPortal = jest.fn()

  global.j = { openCustomerPortal }

  render(<BillingWarningChip />)

  userEvent.click(screen.getByText('Outstanding Balance').closest('button'))

  const openCustomerPortalCalls = window.j.openCustomerPortal.mock.calls

  expect(openCustomerPortalCalls.length).toEqual(1)
  expect(openCustomerPortalCalls[0][0]).toEqual('BILLING_HISTORY')

  delete global.j
})
