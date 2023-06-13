import { atom } from 'recoil'

const bankAccount = atom({
  key: 'bankAccount',
  default: {
    transit_number: null,
    institution_number: null,
    routing_number: null,
    account_number: null,
    account_type: 'checking',
    account_holder_type: 'personal',
    mandate_accepted: false,
  },
})

export default bankAccount
