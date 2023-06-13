import { memo, useContext } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import { supportedPrimaryColors } from '@/constants/styleConstants'
import styles from '@/components/Variant/Variant.scss'

const Variant = ({ variant }) => {
  const { variants, theme, primaryColor } = useContext(StoreContext)
  const isActive = variant.id === variants.chosen.id
  const isLightTheme = theme === 'light'
  const { bgColor, textColor, bgColorPale } = supportedPrimaryColors[primaryColor] || {}

  const handleVariantChange = (e) => {
    const chosenId = parseInt(e.target.value, 10)

    variants.set(variants.all.find((variant) => variant.id === chosenId))
  }

  return (
    <label
      aria-label={variant.title}
      className={classnames(
        styles.root,
        isLightTheme && styles.light,
        isActive && styles.active,
        isActive && isLightTheme && bgColor,
        isActive && !isLightTheme && `${textColor} ${bgColorPale}`
      )}
    >
      <input
        type='radio'
        className={classnames('form-radio', styles.hiddenInput)}
        onChange={handleVariantChange}
        value={variant.id}
        checked={isActive}
      />
      {variant.title}
    </label>
  )
}

Variant.propTypes = {
  variant: PropTypes.shape({
    id: PropTypes.number,
    title: PropTypes.string,
  }),
}

export default memo(Variant)
