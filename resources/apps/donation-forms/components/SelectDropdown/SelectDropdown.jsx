import { memo, useState } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { except } from '@/utilities/object'
import { noop } from '@/utilities/helpers'
import { isPrimaryColourDark, primaryColour400, primaryColourOrBlack } from '@/utilities/theme'
import styles from './SelectDropdown.scss'

const SelectDropdown = ({
  className,
  defaultValue,
  clean = false,
  compact = false,
  onChange = noop,
  options,
  ...unhandledProps
}) => {
  const [value, setValue] = useState(defaultValue || '')

  const backgroundColour = clean ? primaryColour400 : primaryColourOrBlack

  const backgroundImageSvg = `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'><path stroke='${backgroundColour}' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/></svg>`

  const handleOnChange = (e) => {
    setValue(e.target.value)
    onChange(e)
  }

  const selectedOption = options.find((option) => option.value == value)

  return (
    <div
      className={classnames(
        styles.root,
        isPrimaryColourDark && styles.darkPrimaryColour,
        className,
        clean && styles.clean,
        compact && styles.compact
      )}
      style={{
        backgroundImage: `url("data:image/svg+xml,${encodeURIComponent(backgroundImageSvg)}")`,
      }}
    >
      <select onChange={handleOnChange} value={value} {...except(unhandledProps, ['value'])}>
        {options.map((option) => {
          return (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          )
        })}
      </select>

      <div className={styles.select}>{selectedOption?.selected || selectedOption?.label || value}</div>
    </div>
  )
}

SelectDropdown.propTypes = {
  className: PropTypes.string,
  name: PropTypes.string,
  defaultValue: PropTypes.any,
  clean: PropTypes.bool,
  compact: PropTypes.bool,
  onChange: PropTypes.func,
  options: PropTypes.array,
}

export default memo(SelectDropdown)
