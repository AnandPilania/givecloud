import { atom } from 'recoil'

const fundraiseAccessState = atom({
  key: 'fundraiseAccessState',
  default: window?.adminSpaData?.fundraiseEarlyAccessStatus || false,
})

export default fundraiseAccessState
