import { forwardRef, memo } from 'react'
import PropTypes from 'prop-types'
import { useHistory } from 'react-router-dom'
import classnames from 'classnames'
import { noop } from '@/utilities/helpers'
import styles from './Button.scss'

const Button = forwardRef((props, ref) => {
  let { className, to, onClick = noop, outline = false, disabled = false, children, ...unhandledProps } = props

  const history = useHistory()

  const handleOnClick = (e) => {
    if (disabled) {
      e.preventDefault()
      return
    }

    if (to) {
      history.push(to)
    }

    onClick(e)
  }

  return (
    <button
      ref={ref}
      className={classnames(styles.root, outline && styles.outline, className)}
      disabled={disabled}
      onClick={handleOnClick}
      {...unhandledProps}
    >
      {children}
    </button>
  )
})

Button.displayName = 'Button'

Button.propTypes = {
  className: PropTypes.string,
  to: PropTypes.oneOfType([PropTypes.string, PropTypes.object]),
  onClick: PropTypes.func,
  outline: PropTypes.bool,
  disabled: PropTypes.bool,
  children: PropTypes.node,
}

export default memo(Button)
