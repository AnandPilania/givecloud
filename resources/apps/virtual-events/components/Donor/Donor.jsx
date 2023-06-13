import { memo } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import Emoji from 'react-emoji-render'
import formatMoney from '@/utilities/formatMoney'
import styles from '@/components/Donor/Donor.scss'

const Donor = ({
  themeStyle,
  themePrimaryColor,
  name,
  amount,
  type,
  location,
  celebrationThreshold,
}) => {
  const isBigDonation = amount >= celebrationThreshold

  return (
    <div
      className={classnames(
        styles.root,
        themeStyle === 'dark' ? styles.dark : styles.light,
        styles[themePrimaryColor],
        isBigDonation && styles.bigDonation
      )}
    >
      {isBigDonation && (
        <span className={styles.party}>
          <Emoji text='🎉' />
        </span>
      )}

      <div className={styles.content}>
        <span className={styles.name}>{name || '(Anonymous)'}&nbsp;</span>
        {location && <span>from {location}&nbsp;</span>}
        <span>{type == 'pledge' ? 'pledged' : 'donated'}&nbsp;</span>
        {formatMoney(amount)}
      </div>
    </div>
  )
}

Donor.propTypes = {
  themeStyle: PropTypes.string,
  themePrimaryColor: PropTypes.string,
  name: PropTypes.string,
  amount: PropTypes.number,
  type: PropTypes.string,
  location: PropTypes.string,
  celebrationThreshold: PropTypes.string,
}

export default memo(Donor)
