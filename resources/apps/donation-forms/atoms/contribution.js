import { atom, selector } from 'recoil'
import formInputState from './formInput'

const contributionState = atom({
  key: 'contributionState',
  default: null,
})

const contribution = selector({
  key: 'contribution',
  get: ({ get }) => {
    return get(contributionState)
  },
  set: ({ get, set }, newValue) => {
    set(contributionState, newValue)
    set(formInputState, { ...get(formInputState), cart_id: newValue?.id })
  },
})

export default contribution
