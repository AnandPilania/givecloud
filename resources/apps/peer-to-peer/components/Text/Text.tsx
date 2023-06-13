import type { FC, HTMLProps } from 'react'
import { PRIMARY, CUSTOM_THEME as CUSTOM } from '@/shared/constants/theme'
import { createElement } from 'react'
import classnames from 'classnames'
import { TextType } from '@/shared/constants/theme'
import styles from './Text.styles.scss'

type Theme = typeof PRIMARY | typeof CUSTOM

interface Props extends HTMLProps<HTMLDivElement> {
  type?: TextType
  isBold?: boolean
  isMarginless?: boolean
  isTruncated?: boolean
  isSecondaryColour?: boolean
  theme?: Theme
}

const Text: FC<Props> = ({
  isBold,
  isTruncated,
  isMarginless,
  type = 'p',
  children,
  isSecondaryColour,
  className,
  theme = PRIMARY,
  ...rest
}) => {
  const element = type === 'footnote' ? 'p' : type ?? 'p'

  const props = {
    ...rest,
    className: classnames(
      styles.root,
      styles[type],
      styles[theme],
      isBold && styles.bold,
      isMarginless ? styles.noMargin : styles.marginBottom,
      isTruncated && 'truncate',
      isSecondaryColour && styles.secondary,
      className
    ),
  }
  return createElement(element, props, children)
}

export { Text }
