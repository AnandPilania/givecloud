import { memo } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { noop } from '@/utilities/helpers'
import styles from './Checkbox.scss'
import { primaryColour500, primaryColourWhiteOrBlack } from '@/utilities/theme'

const Checkbox = ({ className, id, checked, inverted, onChange = noop, children, ...unhandledProps }) => {
  const backgroundColour = checked ? (inverted && primaryColour500) || primaryColourWhiteOrBlack : 'none'
  const backgroundImageSvg = `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='${backgroundColour}'><path d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/></svg>`

  const handleOnChange = (e) => {
    onChange(e)
  }

  return (
    <div className={classnames(styles.root, className, inverted && styles.inverted)}>
      <div className={styles.input}>
        <input
          style={{
            backgroundImage: `url("data:image/svg+xml,${encodeURIComponent(backgroundImageSvg)}")`,
          }}
          type='checkbox'
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

Checkbox.propTypes = {
  id: PropTypes.string,
  className: PropTypes.string,
  checked: PropTypes.bool,
  inverted: PropTypes.bool,
  onChange: PropTypes.func,
  children: PropTypes.node,
}

export default memo(Checkbox)
