import { memo } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { noop } from '@/utilities/helpers'
import styles from './RadioButton.scss'

const RadioButton = ({ className, id, checked, onChange = noop, children, ...unhandledProps }) => {
  const backgroundImageSvg = `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='white'><circle cx='8' cy='8' r='3'/></svg>`

  const handleOnChange = (e) => {
    onChange(e)
  }

  return (
    <div className={classnames(styles.root, className)}>
      <div className={styles.input}>
        <input
          style={{
            backgroundImage: `url("data:image/svg+xml,${encodeURIComponent(backgroundImageSvg)}")`,
          }}
          type='radio'
          {...(id && { id })}
          checked={checked}
          onChange={handleOnChange}
          {...unhandledProps}
        />
      </div>
      <div className={styles.label}>
        <label {...(id && { htmlFor: id })}>{children}</label>
      </div>
    </div>
  )
}

RadioButton.propTypes = {
  id: PropTypes.string,
  className: PropTypes.string,
  checked: PropTypes.bool,
  onChange: PropTypes.func,
  children: PropTypes.node,
}

export default memo(RadioButton)
