import type { FC } from 'react'
import type { InputProps } from '@/aerosol/Input'
import { forwardRef } from 'react'
import { Input } from '@/aerosol/Input'
import { formatMoney } from '@/shared/utilities/formatMoney'

const stringCurrencyToNumber = (number: string) => Number(number.replace(/[^0-9\.-]+/g, ''))

export type onChangeType = {
  name: string
  value: number
}

interface Props extends Omit<InputProps, 'value' | 'onChange'> {
  currency: string
  isChecked?: boolean
  value: number
  onChange: (target: onChangeType) => void
}

const CurrencyInput: FC<Props> = forwardRef(({ currency, onChange, value, isChecked, ...rest }, ref) => {
  const formattedValue = formatMoney({ amount: value, currency, digits: 0 })

  return (
    <Input
      {...rest}
      ref={ref}
      isMarginless
      isDisabled={!isChecked}
      value={formattedValue}
      onChange={({ target }) =>
        onChange?.({
          ...target,
          value: stringCurrencyToNumber(target?.value),
          name: target?.name,
        })
      }
    />
  )
})

CurrencyInput.displayName = 'CurrencyInput'

CurrencyInput.defaultProps = {
  isLabelHidden: false,
}

export { CurrencyInput }
