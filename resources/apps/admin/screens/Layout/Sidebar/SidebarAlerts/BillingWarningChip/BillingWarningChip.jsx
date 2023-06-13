import { Chip } from '@/aerosol'
import { ERROR } from '@/shared/constants/theme'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faExclamationTriangle } from '@fortawesome/pro-regular-svg-icons'

const BillingWarningChip = () => (
  <Chip theme={ERROR} onClick={() => window?.j?.openCustomerPortal?.('BILLING_HISTORY')}>
    <FontAwesomeIcon icon={faExclamationTriangle} />
    <span className='ml-2'>
      <strong>Outstanding Balance</strong>
    </span>
  </Chip>
)

export { BillingWarningChip }
