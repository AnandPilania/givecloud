import { atom, selector } from 'recoil'

const amountSelectorState = atom({
  key: 'amountSelectorState',
  default: {
    amountChanged: false,
    minusClicked: false,
    plusClicked: false,
  },
})

const amountSelector = selector({
  key: 'amountSelector',
  get: ({ get }) => {
    return get(amountSelectorState)
  },
  set: ({ get, set }, newValue) => {
    set(amountSelectorState, { ...get(amountSelectorState), ...newValue })
  },
})

export default amountSelector
