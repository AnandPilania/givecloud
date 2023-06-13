import { atom } from 'recoil'

const paymentStatus = atom({
  key: 'paymentStatus',
  default: null,
})

export default paymentStatus
