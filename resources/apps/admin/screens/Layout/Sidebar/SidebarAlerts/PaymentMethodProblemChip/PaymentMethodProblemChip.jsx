import { Chip } from '@/aerosol/Chip'
import { ERROR } from '@/shared/constants/theme'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faExclamationTriangle } from '@fortawesome/pro-regular-svg-icons'

const PaymentMethodProblemChip = () => (
  <Chip theme={ERROR} onClick={() => window?.j?.openCustomerPortal?.('ADD_PAYMENT_SOURCE')}>
    <FontAwesomeIcon icon={faExclamationTriangle} />
    <span className='ml-2'>
      <strong>Payment Method Problem</strong>
    </span>
  </Chip>
)

export { PaymentMethodProblemChip }
