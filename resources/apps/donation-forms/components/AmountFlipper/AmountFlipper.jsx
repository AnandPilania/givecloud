import { memo } from 'react'
import { useRecoilValue } from 'recoil'
import { useWindowSize } from 'react-use'
import PropTypes from 'prop-types'
import FlipNumbers from 'react-flip-numbers'
import useCurrencyFormatter from '@/hooks/useCurrencyFormatter'
import formInputState from '@/atoms/formInput'
import styles from './AmountFlipper.scss'

const AmountFlipper = ({ small }) => {
  const { width: windowWidth } = useWindowSize()

  const formInput = useRecoilValue(formInputState)
  const formatCurrency = useCurrencyFormatter()

  const amount = formatCurrency(formInput.item.amt)

  const computeHeight = () => {
    if (small) {
      return 58
    }

    let height = 75

    if (windowWidth > 372) {
      height = 95
    } else if (windowWidth > 342) {
      height = 85
    }

    const digits = amount.match(/[\d]/g).length + 1 // 0-9 and the currency symbol
    const separators = Math.max(0, amount.length - digits) // ,.

    if (digits > 4) {
      height -= 8 * separators
      height -= 10 * Math.max(0, digits - 4)
    }

    return height
  }

  const fontWeight = small ? 600 : 'auto'
  const widthRatio = small ? 0.58 : 0.55

  const flipNumbersProps = (height) => ({
    width: Math.round(height * widthRatio),
    height,
    duration: 0.5,
    numbers: amount,
    perspective: 'none',
    nonNumberStyle: {
      display: 'flex',
      alignItems: 'center',
      fontSize: Math.round(height * 0.9),
      height,
      fontWeight,
    },
    numberStyle: {
      fontWeight,
    },
  })

  return (
    <div className={styles.root}>
      <FlipNumbers play {...flipNumbersProps(computeHeight())} />
    </div>
  )
}

AmountFlipper.propTypes = {
  small: PropTypes.bool,
}

export default memo(AmountFlipper)
