import { memo, useContext } from 'react'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import { supportedPrimaryColors } from '@/constants/styleConstants'
import styles from '@/components/NextStepButton/NextStepButton.scss'

const NextStepButton = () => {
  const { processStep, submissionInput, paymentInput, primaryColor, theme } = useContext(
    StoreContext
  )

  const {
    bgColor,
    hoverBgColorLight,
    focusBorderColorDark,
    focusRingColorLight,
    focusRingColorDark,
    activeBgColorDark,
  } = supportedPrimaryColors[primaryColor] || {}

  const isLightTheme = theme === 'light'

  return (
    <button
      aria-label='Go to the Next Step'
      type='button'
      onClick={() => {
        processStep.next(submissionInput, paymentInput)
      }}
      className={classnames(
        styles.root,
        bgColor,
        hoverBgColorLight,
        focusBorderColorDark,
        activeBgColorDark,
        focusRingColorLight,
        !isLightTheme && focusRingColorDark
      )}
    >
      Next
    </button>
  )
}

export default memo(NextStepButton)
