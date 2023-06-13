import { memo } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import FlipNumbers from 'react-flip-numbers'
import styles from '@/components/Counter/Counter.scss'

const Counter = ({ themeStyle, value, isInitialized, type = 'full' }) => {
  if (isInitialized) {
    const formatConfig = {
      maximumFractionDigits: 0,
    }

    // setup formatters
    const americanNumberFormatter = new Intl.NumberFormat('en-US', formatConfig)

    // use formatters
    value = americanNumberFormatter.format(value)
  }

  const isTypeDashboard = type === 'dashboard'
  const flipnumbersize = isTypeDashboard ? { w: 55, h: 70 } : { w: 50, h: 65 }

  return (
    <div
      className={classnames(
        styles.root,
        !isInitialized && styles.uninitalized,
        isTypeDashboard && styles.dashboardType,
        themeStyle === 'dark' ? styles.dark : styles.light
      )}
    >
      {isInitialized && <span className={styles.dollarSign}>$</span>}

      <FlipNumbers
        play
        width={flipnumbersize.w}
        height={flipnumbersize.h}
        numbers={`${isInitialized ? value : 'Loading'}`}
      />
    </div>
  )
}

Counter.propTypes = {
  themeStyle: PropTypes.string,
  value: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
  isInitialized: PropTypes.bool,
  type: PropTypes.string,
}

export default memo(Counter)
