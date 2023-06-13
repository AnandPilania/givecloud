import { memo, useState } from 'react'
import { useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import configState from '@/atoms/config'
import AmountFlipperContainer from '@/components/AmountFlipperContainer/AmountFlipperContainer'
import VariantButton from '@/components/VariantButton/VariantButton'
import AmountTiles from './AmountTiles/AmountTiles'
import styles from './AmountSelector.scss'

const AmountSelector = ({ small }) => {
  const config = useRecoilValue(configState)
  const showVariants = config.variants.length > 1

  const [showAmountInput, setShowAmountInput] = useState(false)
  const [showTabToChange, setShowTabToChange] = useState(false)

  return (
    <div className={styles.root}>
      <div onMouseEnter={() => setShowTabToChange(true)} onMouseLeave={() => setShowTabToChange(false)}>
        <AmountFlipperContainer showTabToChange={showTabToChange} setShow={setShowAmountInput} small={small} />
      </div>

      <span className={styles.showVariants}>
        {showVariants && config.variants.map((variant) => <VariantButton key={variant.id} variant={variant} />)}
      </span>
      <AmountTiles showInput={showAmountInput} setShowInput={setShowAmountInput} />
    </div>
  )
}

AmountSelector.propTypes = {
  small: PropTypes.bool,
}

export default memo(AmountSelector)
