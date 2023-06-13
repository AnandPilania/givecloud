import type { FC, ReactNode, HTMLProps } from 'react'
import { createElement } from 'react'
import classnames from 'classnames'
import { TextType } from '@/shared/constants/theme'
import styles from './Text.styles.scss'

interface Props extends HTMLProps<HTMLElement> {
  type?: TextType
  isError?: boolean
  isBold?: boolean
  isMarginless?: boolean
  isTruncated?: boolean
  isSecondaryColour?: boolean
  children?: ReactNode | ReactNode[]
}

const Text: FC<Props> = ({
  isTruncated,
  isMarginless,
  isBold,
  type,
  children,
  isSecondaryColour,
  isError,
  className,
  ...rest
}) => {
  const element = type === 'footnote' ? 'p' : type ?? 'p'

  const props = {
    ...rest,
    className: classnames(
      styles.root,
      styles[type ?? 'p'],
      isError && styles.error,
      isMarginless ? styles.noMargin : styles.marginBottom,
      isBold && styles.bold,
      isTruncated && 'truncate',
      isSecondaryColour && styles.secondaryColour,
      className
    ),
  }
  return createElement(element, props, children)
}

Text.defaultProps = {
  isSecondaryColour: false,
  type: 'p',
  isBold: false,
  isMarginless: false,
  isTruncated: false,
}

export { Text }
