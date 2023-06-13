import { atom } from 'recoil'

const screenHeader = atom({
  key: 'screenHeader',
  default: {
    showHeader: false,
    showBackButton: false,
    showCloseButton: false,
    showLocaleSwitcher: false,
  },
})

export default screenHeader
