import { memo, useContext } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Input from '@/fields/Input/Input'
import styles from '@/components/AmountSelector/AmountSelectorCustomAmountField.scss'

const AmountSelectorCustomAmountField = ({ value, error, onChangeAmount }) => {
  const { theme } = useContext(StoreContext)
  const isLightTheme = theme === 'light'

  return (
    <div className={styles.root}>
      <Label title='Amount' error={error}>
        <div className={classnames(styles.dollarSign, isLightTheme && styles.light)}>$</div>

        <Input
          className={`${styles.input} w-3/4`}
          hasError={!!error}
          type='number'
          value={value}
          onChange={onChangeAmount}
          step='0.01'
          autoComplete='off'
        />
      </Label>
    </div>
  )
}

AmountSelectorCustomAmountField.propTypes = {
  value: PropTypes.string,
  error: PropTypes.string,
  onChangeAmount: PropTypes.func,
}

export default memo(AmountSelectorCustomAmountField)
