import { memo } from 'react'
import PropTypes from 'prop-types'
import DonorList from '@/components/DonorList/DonorList'
import Counter from '@/components/Counter/Counter'
import styles from '@/components/Updates/Updates.scss'

const Updates = ({
  themeStyle,
  themePrimaryColor,
  isAmountTallyEnabled,
  isHonorRollEnabled,
  donationTotal,
  isInitialized,
  donors,
  celebrationThreshold,
  type = 'full',
  totalText = '',
  totalTextColour = '',
}) => (
  <div className={styles.root}>
    {(!!isAmountTallyEnabled || !isInitialized) && (
      <div className={styles.totalContainer}>
        <Counter
          themeStyle={themeStyle}
          type={type}
          value={donationTotal}
          isInitialized={isInitialized}
        />

        {!!isInitialized && (
          <div
            className={styles.totalText}
            style={totalTextColour ? { color: totalTextColour } : {}}
          >
            {totalText || 'total donated'}
          </div>
        )}
      </div>
    )}

    {!!isHonorRollEnabled && (
      <div className={styles.donorListContainer}>
        <DonorList
          themeStyle={themeStyle}
          themePrimaryColor={themePrimaryColor}
          type={type}
          donors={donors}
          celebrationThreshold={celebrationThreshold}
        />
      </div>
    )}
  </div>
)

Updates.propTypes = {
  themeStyle: PropTypes.string,
  themePrimaryColor: PropTypes.string,
  isAmountTallyEnabled: PropTypes.oneOf([0, 1]),
  isHonorRollEnabled: PropTypes.oneOf([0, 1]),
  donationTotal: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
  isInitialized: PropTypes.bool,
  donors: PropTypes.array,
  celebrationThreshold: PropTypes.string,
  type: PropTypes.string,
  totalText: PropTypes.string,
  totalTextColour: PropTypes.string,
}

export default memo(Updates)
