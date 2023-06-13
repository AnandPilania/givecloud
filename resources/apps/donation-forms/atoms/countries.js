import { atom } from 'recoil'

const countries = atom({
  key: 'countries',
  default: null,
})

export default countries
