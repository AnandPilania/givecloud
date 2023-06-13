import { atom } from 'recoil'

const hostedPaymentFields = atom({
  key: 'hostedPaymentFields',
  default: {
    number: null,
    exp: null,
    cvv: null,
  },
})

export default hostedPaymentFields
