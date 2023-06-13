import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Select from '@/fields/Select/Select'
import getNumberWithOrdinal from '@/utilities/getNumberWithOrdinal'
import styles from '@/components/RecurrenceSelector/RecurrenceSelector.scss'

const shouldRender = (isWeekly, billingPeriod, recurrence) => {
  // Donation is not recurring
  if (billingPeriod === 'onetime') {
    return false
  }

  // Recurrence starts on the day of the week/month that the donation was made
  if (recurrence.scheduleType === 'natural') {
    return false
  }

  // Week Based but only one weekday option
  if (isWeekly && recurrence.weekday.options.length === 1) {
    return false
  }

  // Day Based but only one day option
  if (!isWeekly && recurrence.day.options.length === 1) {
    return false
  }

  return true
}

const RecurrenceSelector = () => {
  const { variants, recurrence } = useContext(StoreContext)
  const billingPeriod = variants.chosen.billing_period
  const isWeekly = billingPeriod === 'weekly' || billingPeriod === 'biweekly'

  if (!shouldRender(isWeekly, billingPeriod, recurrence)) {
    return null
  }

  const onChangeWeekday = (e) => {
    const value = e.target.value

    recurrence.weekday.set(value)
  }

  const onChangeDay = (e) => {
    const value = e.target.value

    recurrence.day.set(value)
  }

  return (
    <div className={styles.root}>
      <Label title='Payment Day'>
        {isWeekly ? (
          <Select value={recurrence.weekday.chosen} onChange={onChangeWeekday}>
            {Object.keys(recurrence.weekday.options).map((index) => (
              <option key={index} value={index}>
                {recurrence.weekday.options[index]}
              </option>
            ))}
          </Select>
        ) : (
          <Select value={recurrence.day.chosen} onChange={onChangeDay}>
            {recurrence.day.options.map((day) => (
              <option key={day} value={day}>
                {getNumberWithOrdinal(day)} of the Month
              </option>
            ))}
          </Select>
        )}
      </Label>
    </div>
  )
}

export default memo(RecurrenceSelector)
