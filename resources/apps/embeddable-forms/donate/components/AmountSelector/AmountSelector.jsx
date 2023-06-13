import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import formatMoney from '@/utilities/formatMoney'
import AmountSelectorPresets from '@/components/AmountSelector/AmountSelectorPresets'
import AmountSelectorCustomAmountField from '@/components/AmountSelector/AmountSelectorCustomAmountField'
import ErrorBox from '@/components/ErrorBox/ErrorBox'
import styles from '@/components/AmountSelector/AmountSelector.scss'

const AmountSelector = () => {
  const { variants, donation, formErrors, minimumDonation, currency } = useContext(StoreContext)
  const value = donation.value
  const error = formErrors.all['item.amt']

  const onChangePreset = (e) => {
    const value = e.target.value

    donation.set(value)
    donation.preset.set(value)
  }

  const onChangeAmount = (e) => {
    const value = e.target.value

    donation.set(value)
    donation.preset.set('other')
  }

  return (
    <div className={styles.root}>
      {variants.chosen.price_presets ? (
        <div className={styles.presetsContainer}>
          <AmountSelectorPresets
            value={value}
            chosenPreset={donation.preset.chosen}
            onChangePreset={onChangePreset}
            onChangeAmount={onChangeAmount}
          />

          {error && (
            <div className={styles.error}>
              <ErrorBox aria-invalid>
                Please choose a valid donation amount. (Minimum:{' '}
                {formatMoney(minimumDonation, currency.chosen.code)})
              </ErrorBox>
            </div>
          )}
        </div>
      ) : (
        <AmountSelectorCustomAmountField
          value={value}
          error={error}
          onChangeAmount={onChangeAmount}
        />
      )}
    </div>
  )
}

export default memo(AmountSelector)
