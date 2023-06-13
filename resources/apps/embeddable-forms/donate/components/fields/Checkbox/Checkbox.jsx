import { memo, useContext } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import { supportedPrimaryColors } from '@/constants/styleConstants'
import styles from '@/fields/Checkbox/Checkbox.scss'

const Checkbox = ({ checked = false, children, ...props }) => {
  const { primaryColor, theme } = useContext(StoreContext)
  const isLightTheme = theme === 'light'

  const { textColor, focusRingColorLight, focusRingColorDark } =
    supportedPrimaryColors[primaryColor] || {}

  return (
    <div className={styles.root}>
      <div className={styles.inputContainer}>
        <input
          type='checkbox'
          checked={checked}
          className={classnames(
            'form-checkbox',
            styles.input,
            textColor,
            focusRingColorLight,
            !isLightTheme && focusRingColorDark
          )}
          {...props}
        />
      </div>

      <div className={styles.label}>{children}</div>
    </div>
  )
}

Checkbox.propTypes = {
  checked: PropTypes.bool,
  children: PropTypes.node,
}

export default memo(Checkbox)
