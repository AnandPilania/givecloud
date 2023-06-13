import { memo, useContext } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import formatMoney from '@/utilities/formatMoney'
import Radio from '@/fields/Radio/Radio'
import Input from '@/fields/Input/Input'
import styles from '@/components/AmountSelector/AmountSelectorPresets.scss'

const AmountSelectorPresets = ({ chosenPreset, value, onChangeAmount, onChangePreset }) => {
  const { variants, currency, presetsOtherLabel, theme, variantDescriptions } = useContext(
    StoreContext
  )
  const presets = variants.chosen.price_presets
  const isLightTheme = theme === 'light'
  const variantPresetDescription = variantDescriptions[variants.chosen.id]

  const hasOnlyWholeNumberPresets =
    presets
      .filter((preset) => {
        // only the numbers
        return !isNaN(parseFloat(preset))
      })
      .filter((preset) => {
        // filter out whole numbers
        return preset % 1 !== 0
      }).length === 0

  return (
    <div className={styles.root}>
      {presets.map((preset) => {
        const formattedPresetAmount = formatMoney(
          preset,
          currency.chosen.code,
          hasOnlyWholeNumberPresets ? 0 : 2
        )
        const descriptor = variantPresetDescription && variantPresetDescription[preset]

        return (
          <Radio
            key={preset}
            name='donationAmountInput'
            aria-label={`Donation amount ${preset}`}
            checked={chosenPreset === preset}
            onChange={onChangePreset}
            value={preset}
          >
            {preset === 'other' ? (
              <div className={styles.otherAmount}>
                <div className={classnames(styles.dollarSign, isLightTheme && styles.light)}>$</div>

                <Input
                  aria-label='input other amount'
                  className={styles.input}
                  onChange={onChangeAmount}
                  placeholder={presetsOtherLabel}
                  type='number'
                  step='0.01'
                  value={chosenPreset !== 'other' ? '' : value}
                  autoComplete='off'
                />
              </div>
            ) : (
              formattedPresetAmount
            )}
            {!!descriptor && <span className='ml-2 opacity-75'>({descriptor})</span>}
          </Radio>
        )
      })}
    </div>
  )
}

AmountSelectorPresets.propTypes = {
  chosenPreset: PropTypes.string,
  value: PropTypes.string,
  onChangeAmount: PropTypes.func,
  onChangePreset: PropTypes.func,
}

export default memo(AmountSelectorPresets)
