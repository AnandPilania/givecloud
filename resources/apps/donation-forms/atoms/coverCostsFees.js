import { selector } from 'recoil'
import { round } from 'lodash'
import Givecloud from 'givecloud'
import formInputState from './formInput'

const coverCostsFees = selector({
  key: 'coverCostsFees',
  get: ({ get }) => {
    const config = Givecloud.config.processing_fees
    const formInput = get(formInputState)

    if (config.using_ai) {
      return Givecloud.Dcc.getCost(formInput.item.amt, formInput.cover_costs_type)
    }

    return round(config.amount + (formInput.item.amt * config.rate) / 100, 2)
  },
})

export default coverCostsFees
