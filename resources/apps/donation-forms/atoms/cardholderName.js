import { selector } from 'recoil'
import { firstName, lastName } from '@/utilities/string'
import cardholderDataState from './cardholderData'
import formInputState from './formInput'

const cardholderName = selector({
  key: 'cardholderName',
  get: ({ get }) => {
    return get(cardholderDataState).name
  },
  set: ({ get, set }, newValue) => {
    const formInput = get(formInputState)
    const cardholderData = get(cardholderDataState)

    set(formInputState, {
      ...formInput,
      billing_first_name: firstName(newValue) || null,
      billing_last_name: lastName(newValue) || null,
    })

    set(cardholderDataState, { ...cardholderData, name: newValue || null })
  },
})

export default cardholderName
