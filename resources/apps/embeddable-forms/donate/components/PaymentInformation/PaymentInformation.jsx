import { memo, useContext } from 'react'
import PropTypes from 'prop-types'
import { StoreContext } from '@/root/store'
import PaymentMethodSelector from '@/components/PaymentMethodSelector/PaymentMethodSelector'
import CreditCard from '@/components/CreditCard/CreditCard'
import BankAccount from '@/components/BankAccount/BankAccount'
import PaypalDisclaimer from '@/components/PaypalDisclaimer/PaypalDisclaimer'

const PaymentInformation = ({ className }) => {
  const { payment } = useContext(StoreContext)

  return (
    <div className={className}>
      <PaymentMethodSelector />
      {payment.method.chosen === 'credit_card' && <CreditCard />}
      {payment.method.chosen === 'bank_account' && <BankAccount />}
      {payment.method.chosen === 'paypal' && <PaypalDisclaimer />}
    </div>
  )
}

PaymentInformation.propTypes = {
  className: PropTypes.string,
}

export default memo(PaymentInformation)
