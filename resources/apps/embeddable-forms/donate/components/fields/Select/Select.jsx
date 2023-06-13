import { memo, useContext } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import styles from '@/fields/Select/Select.scss'
import { supportedPrimaryColors } from '@/constants/styleConstants'

const Select = ({ width = 'full', hasError = false, children, ...props }) => {
  const { theme, primaryColor } = useContext(StoreContext)
  const { focusRingColorLight, focusRingColorDark } = supportedPrimaryColors[primaryColor] || {}
  const isLightTheme = theme === 'light'

  return (
    <select
      className={classnames(
        `w-${width} form-select`,
        styles.root,
        focusRingColorLight,
        !isLightTheme && focusRingColorDark,
        isLightTheme && styles.light,
        hasError && styles.error
      )}
      {...props}
    >
      {children}
    </select>
  )
}

Select.propTypes = {
  width: PropTypes.string,
  hasError: PropTypes.bool,
  children: PropTypes.node,
}

export default memo(Select)
