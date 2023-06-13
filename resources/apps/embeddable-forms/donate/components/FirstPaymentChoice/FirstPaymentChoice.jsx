import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Checkbox from '@/fields/Checkbox/Checkbox'
import styles from '@/components/FirstPaymentChoice/FirstPaymentChoice.scss'

const FirstPaymentChoice = () => {
  const { recurrence } = useContext(StoreContext)

  if (!recurrence.showOptionalFirstPaymentToday) {
    return null
  }

  const handleChange = (e) => {
    const value = e.target.checked

    recurrence.firstPaymentToday.set(value)
  }

  return (
    <div className={styles.root}>
      <Label>
        <Checkbox value='1' checked={recurrence.firstPaymentToday.value} onChange={handleChange}>
          Make my first payment today
        </Checkbox>
      </Label>
    </div>
  )
}

export default memo(FirstPaymentChoice)
