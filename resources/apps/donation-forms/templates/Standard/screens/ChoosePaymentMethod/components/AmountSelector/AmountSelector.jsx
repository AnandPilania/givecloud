import { memo } from 'react'
import { useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import AmountStepper from './components/AmountStepper/AmountStepper'
import VariantButton from '@/components/VariantButton/VariantButton'
import configState from '@/atoms/config'
import styles from './AmountSelector.scss'

const AmountSelector = ({ small }) => {
  const config = useRecoilValue(configState)
  const showVariants = config.variants.length > 1

  return (
    <div className={styles.root}>
      <div className={styles.content}>
        <div className={styles.presets}>
          <AmountStepper small={small} />
        </div>

        {showVariants && (
          <div className={styles.variants}>
            {config.variants.map((variant) => (
              <VariantButton key={variant.id} variant={variant} />
            ))}
          </div>
        )}
      </div>
    </div>
  )
}

AmountSelector.propTypes = {
  small: PropTypes.bool,
}

export default memo(AmountSelector)
