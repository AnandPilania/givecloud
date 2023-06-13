import { selector } from 'recoil'
import { getCurrency } from '@/utilities/currency'
import cardholderDataState from './cardholderData'
import formInputState from './formInput'

const currency = selector({
  key: 'currency',
  get: ({ get }) => {
    const currencyCode = get(formInputState).currency_code

    return getCurrency(currencyCode)
  },
  set: ({ get, set }, newValue) => {
    const cardholderData = get(cardholderDataState)
    const formInput = get(formInputState)

    set(cardholderDataState, {
      ...cardholderData,
      currency: newValue,
    })

    set(formInputState, {
      ...formInput,
      currency_code: newValue,
    })
  },
})

export default currency
