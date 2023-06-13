import isDevice from '@/utilities/isDevice'

const getPaypalAvailability = (Givecloud, billingPeriod) => {
  if (isDevice()) {
    return false
  }
  var gateway = Givecloud.PaymentTypeGateway('paypal')
  if (gateway && (billingPeriod === 'onetime' || gateway.referenceTransactions)) {
    return true
  } else {
    return false
  }
}

export default getPaypalAvailability
