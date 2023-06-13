import classnames from 'classnames'
import PropTypes from 'prop-types'
import { useWindowSize } from 'react-use'
import { Thermometer } from '@/aerosol'
import { SCREEN_LARGE } from '@/constants/breakpointConstants'
import useCurrencyFormatter from '@/hooks/useCurrencyFormatter'
import styles from './AmountThermometer.scss'

const AmountThermometer = ({ layout, thumbnail, title, amountRaised, goalAmount, currentAmount, currencyCode }) => {
  const formatCurrency = useCurrencyFormatter({ abbreviate: true, currencyCode })
  const { width: windowWidth } = useWindowSize()

  const isLargeScreen = windowWidth >= SCREEN_LARGE
  const isStandardLayout = layout === 'standard'
  const isLargeScreenStandardLayout = isStandardLayout && isLargeScreen

  return (
    <div className={styles.root}>
      <div className={classnames(styles.challenger, isLargeScreenStandardLayout && styles.hide)}>
        <img src={thumbnail} className={styles.thumbnail} />
        <h1>{title}</h1>
      </div>

      <div className={styles.thermometerContainer}>
        <p>
          {formatCurrency(amountRaised)}
          <span className={styles.thermometerLabel}>RAISED</span>
        </p>
        <Thermometer
          initialPercentage={(amountRaised / goalAmount) * 100}
          additionalPercentage={(currentAmount / goalAmount) * 100}
          className={styles.thermometer}
          aria-hidden={true}
          theme='custom'
        />
        <p>
          {formatCurrency(goalAmount)}
          <span className={classnames(styles.thermometerLabel, 'text-right')}>GOAL</span>
        </p>
      </div>
    </div>
  )
}

AmountThermometer.propTypes = {
  layout: PropTypes.string,
  thumbnail: PropTypes.string,
  title: PropTypes.string,
  amountRaised: PropTypes.number,
  goalAmount: PropTypes.number,
  currentAmount: PropTypes.number,
  currencyCode: PropTypes.string,
}

export default AmountThermometer
