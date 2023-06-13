import { atom } from 'recoil'

export const errorBag = atom({
  key: 'errorBag',
  default: {},
})

export const shouldValidateBag = atom({
  key: 'shouldValidateBag',
  default: {},
})

export default errorBag
