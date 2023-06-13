import { memo, useContext } from 'react'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import styles from '@/components/PreviousStepButton/PreviousStepButton.scss'
import { supportedPrimaryColors } from '@/constants/styleConstants'

const PreviousStepButton = () => {
  const { processStep, theme, primaryColor } = useContext(StoreContext)
  const isLightTheme = theme === 'light'
  const { focusRingColorLight, focusRingColorDark } = supportedPrimaryColors[primaryColor] || {}

  return (
    <button
      aria-label='Go to the Previous Step'
      type='button'
      onClick={processStep.previous}
      className={classnames(
        styles.root,
        focusRingColorLight,
        isLightTheme && styles.light,
        !isLightTheme && focusRingColorDark
      )}
    >
      Back
    </button>
  )
}

export default memo(PreviousStepButton)
