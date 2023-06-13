import { selector } from 'recoil'
import coverCostsFeesState from './coverCostsFees'
import formInputState from './formInput'

const pendingContribution = selector({
  key: 'pendingContribution',
  get: ({ get }) => {
    const coverCostsFees = get(coverCostsFeesState)
    const formInput = get(formInputState)

    const fees = formInput.cover_costs_enabled ? coverCostsFees : 0
    const amount = formInput.item.amt

    return {
      fees,
      amount,
      total: amount + fees,
      currency_code: formInput.currency_code,
      is_monthly: formInput.item.recurring_frequency === 'monthly',
    }
  },
})

export default pendingContribution
