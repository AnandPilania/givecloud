import type { FC, HTMLProps } from 'react'
import type { FlipNumbersProps } from 'react-flip-numbers'
import { PRIMARY, CUSTOM_THEME as CUSTOM } from '@/shared/constants/theme'
import FlipNumbers from 'react-flip-numbers'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPlus, faMinus } from '@fortawesome/pro-regular-svg-icons'
import { formatMoney } from '@/shared/utilities/formatMoney'
import { closestIndexOf } from '@/shared/utilities'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './AmountSelector.styles.scss'

const amounts = [
  5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 75, 100, 125, 150, 200, 250, 500, 750, 1000, 1500, 2000, 2500, 5000, 7500,
  10000, 12500, 15000, 17500, 20000, 22500, 25000, 50000,
]

interface Props extends Pick<HTMLProps<HTMLDivElement>, 'className'> {
  value: number
  onChange: (value: number) => void
  presetAmounts?: number[]
  currency: string
  flipNumberOptions?: FlipNumbersProps
  maxValue?: number
}

const AmountSelector: FC<Props> = ({
  value,
  onChange,
  presetAmounts = amounts,
  currency,
  flipNumberOptions,
  maxValue = presetAmounts[presetAmounts.length - 1],
  className,
}) => {
  const isValueWholeNumber = Number.isInteger(value)
  const formattedAmount = formatMoney({
    amount: value,
    currency,
    digits: isValueWholeNumber ? 0 : 2,
    showZero: true,
  })
  const { extraSmall } = useTailwindBreakpoints()
  const fontSize = extraSmall.lessThan ? 44 : 56
  const width = extraSmall.lessThan ? 28 : 38

  const isIncrementDisabled = value >= maxValue
  const isDecrementDisabled = value === presetAmounts[0]

  const getIconCss = (isDisabled: boolean) => classNames(styles.icon, isDisabled && styles.disabled)

  const handleIncrement = () => {
    if (isIncrementDisabled) return null

    const selectedIndex = isValueWholeNumber
      ? presetAmounts.findIndex((amount) => amount === value) + 1
      : closestIndexOf(presetAmounts, value)

    if (selectedIndex < presetAmounts.length) {
      onChange(presetAmounts[selectedIndex])
    }
  }

  const handleDecrement = () => {
    if (isDecrementDisabled) return null

    const selectedIndex = closestIndexOf(presetAmounts, value) - 1

    if (selectedIndex >= 0) {
      onChange(presetAmounts[selectedIndex])
    }
  }

  const flipNumberProps = {
    height: fontSize,
    width,
    color: 'black',
    numbers: formattedAmount,
    numberStyle: {
      fontSize,
      fontWeight: 600,
      fontFamily: 'sans-serif',
    },
    nonNumberStyle: {
      display: 'flex',
      alignItems: 'center',
      fontSize,
      fontWeight: 800,
    },
    ...flipNumberOptions,
  }

  return (
    <div className={classNames(styles.root, className)}>
      <button
        type='button'
        className={classNames(styles.button, styles.marginRight)}
        onClick={handleDecrement}
        aria-label='decrease amount'
        aria-disabled={isDecrementDisabled}
      >
        <FontAwesomeIcon icon={faMinus} className={getIconCss(isDecrementDisabled)} />
      </button>
      <div className={styles.numbersContainer}>
        <FlipNumbers play {...flipNumberProps} />
      </div>
      <button
        type='button'
        className={classNames(styles.button, styles.marginLeft)}
        onClick={handleIncrement}
        aria-label='increase amount'
        aria-disabled={isIncrementDisabled}
      >
        <FontAwesomeIcon icon={faPlus} className={getIconCss(isIncrementDisabled)} />
      </button>
    </div>
  )
}

export { AmountSelector }
