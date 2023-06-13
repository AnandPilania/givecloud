import { memo, useContext } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import { supportedPrimaryColors } from '@/constants/styleConstants'
import styles from '@/fields/Radio/Radio.scss'

const Radio = ({ checked = false, children, ...props }) => {
  const { theme, primaryColor } = useContext(StoreContext)
  const isLightTheme = theme === 'light'

  const { textColor, focusRingColorLight, focusRingColorDark } =
    supportedPrimaryColors[primaryColor] || {}

  return (
    <label className={styles.root}>
      <input
        type='radio'
        checked={checked}
        className={classnames(
          'form-radio',
          styles.input,
          textColor,
          focusRingColorLight,
          !isLightTheme && focusRingColorDark
        )}
        {...props}
      />

      <span className={classnames(styles.label, isLightTheme && styles.light)}>{children}</span>
    </label>
  )
}

Radio.propTypes = {
  checked: PropTypes.bool,
  children: PropTypes.node,
}

export default memo(Radio)
