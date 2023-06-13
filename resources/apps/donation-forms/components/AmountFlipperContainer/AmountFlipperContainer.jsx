import { memo } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import useLocalization from '@/hooks/useLocalization'
import useAnalytics from '@/hooks/useAnalytics'
import AmountFlipper from '@/components/AmountFlipper/AmountFlipper'
import styles from './AmountFlipperContainer.scss'

const AmountFlipperContainer = ({ setShow, showTabToChange, small }) => {
  const t = useLocalization('screens.choose_payment_method.amount_stepper')
  const collectEvent = useAnalytics({ collectOnce: true })

  const handleOnClick = () => {
    setShow(true)
    collectEvent({ event_name: 'custom_amount_click' })
  }

  const handleOnKeyDown = (e) => {
    if (e.key === 'Enter') {
      handleOnClick()
    }
  }

  return (
    <div
      className={classnames(styles.root, small && styles.small)}
      tabIndex='0'
      onKeyDown={handleOnKeyDown}
      onClick={handleOnClick}
    >
      <div className={classnames(styles.tapToChange, showTabToChange && styles.show)}>{t('tap_to_change')}</div>
      <AmountFlipper small={small} />
    </div>
  )
}

AmountFlipperContainer.propTypes = {
  small: PropTypes.bool,
  setShow: PropTypes.func.isRequired,
  showTabToChange: PropTypes.bool,
}

AmountFlipperContainer.defaultProps = {
  small: false,
  showTabToChange: false,
}

export default memo(AmountFlipperContainer)
