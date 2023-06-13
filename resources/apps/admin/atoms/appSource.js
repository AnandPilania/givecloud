import { atom, selector } from 'recoil'
import getConfig from '@/utilities/config'

const appSourceState = atom({
  key: 'appSourceState',
  default: null,
})

const appSource = selector({
  key: 'appSource',
  get: ({ get }) => {
    const { initialAppSource } = getConfig()

    return get(appSourceState) || initialAppSource
  },
  set: ({ set }, newValue) => {
    set(appSourceState, newValue)
  },
})

export default appSource
