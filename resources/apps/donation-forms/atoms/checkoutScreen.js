import { atom } from 'recoil'

const checkoutScreen = atom({
  key: 'checkoutScreen',
  default: {
    action: 'PUSH',
    active: null,
  },
})

export default checkoutScreen
