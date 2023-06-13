import { memo, useContext } from 'react'
import Givecloud from 'givecloud'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Checkbox from '@/fields/Checkbox/Checkbox'
import Select from '@/fields/Select/Select'
import formatMoney from '@/utilities/formatMoney'
import styles from './CoverTheFees.scss'

const CoverTheFees = () => {
  const { coverFees, currency, donation } = useContext(StoreContext)
  const usingDccAiPlus = Givecloud.config.processing_fees.using_ai

  const onChange = (e) => {
    if (usingDccAiPlus) {
      const value = e.target.value || null

      coverFees.set(!!value)
      coverFees.setType(value)
    } else {
      coverFees.set(e.target.checked)
    }
  }

  if (!coverFees.show && !usingDccAiPlus) {
    return null
  }

  if (usingDccAiPlus) {
    const coverCostsAmounts = Givecloud.Dcc.getCosts(parseFloat(donation.value || 0))

    return (
      <div className={styles.root}>
        <h4>+ Cover Costs &amp; Fees</h4>
        <p>{coverFees.label}</p>

        {/* prettier-ignore */}
        <Select value={coverFees.type || ''} onChange={onChange}>
          <option key='most_costs' value='most_costs'>{formatMoney(coverCostsAmounts.most_costs, currency.chosen.code)} - Most Costs</option>
          <option key='more_costs' value='more_costs'>{formatMoney(coverCostsAmounts.more_costs, currency.chosen.code)} - More Costs</option>
          <option key='minimum_costs' value='minimum_costs'>{formatMoney(coverCostsAmounts.minimum_costs, currency.chosen.code)} - Minimum Costs</option>
          <option key='no_thank_you' value="">No Thank You</option>
        </Select>
      </div>
    )
  }

  return (
    <Label>
      <Checkbox value='1' checked={coverFees.value} onChange={onChange}>
        {coverFees.label}
      </Checkbox>
    </Label>
  )
}

export default memo(CoverTheFees)
