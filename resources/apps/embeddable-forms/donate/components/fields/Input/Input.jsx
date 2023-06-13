import { memo, useContext } from 'react'
import PropTypes from 'prop-types'
import InputMask from 'react-input-mask'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import styles from '@/fields/Input/Input.scss'
import { supportedPrimaryColors } from '@/constants/styleConstants'

const Input = ({ className = '', hasError = false, mask, maskChars = '', ...props }) => {
  const { theme, primaryColor } = useContext(StoreContext)
  const { focusRingColorLight, focusRingColorDark } = supportedPrimaryColors[primaryColor] || {}
  const isLightTheme = theme === 'light'

  const inputClassName = classnames(
    className,
    `form-input`,
    styles.root,
    focusRingColorLight,
    !isLightTheme && focusRingColorDark,
    isLightTheme && styles.light,
    hasError && styles.error
  )

  return mask ? (
    <InputMask className={inputClassName} {...props} mask={mask} maskChar={maskChars} />
  ) : (
    <input className={inputClassName} {...props} />
  )
}

Input.propTypes = {
  className: PropTypes.string,
  hasError: PropTypes.bool,
  mask: PropTypes.string,
  maskChars: PropTypes.string,
}

export default memo(Input)
