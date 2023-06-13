import type { FC, HTMLProps, ReactNode } from 'react'
import classNames from 'classnames'
import styles from './Box.styles.scss'

export interface BoxProps {
  isOverflowVisible: boolean
  isPaddingless: boolean
  isMarginless: boolean
  isFullscreen: boolean
  isReducedPadding: boolean
  isFullHeight: boolean
}
interface Props extends HTMLProps<HTMLDivElement>, Partial<BoxProps> {
  children: ReactNode
}

const Box: FC<Props> = ({
  children,
  isFullHeight,
  isOverflowVisible,
  isPaddingless,
  isMarginless,
  isFullscreen,
  isReducedPadding,
  className,
  ...rest
}) => {
  const css = classNames(
    styles.root,
    isPaddingless ? styles.noPadding : styles.padding,
    isReducedPadding && styles.reducedPadding,
    isMarginless && styles.noMargin,
    isOverflowVisible && styles.overflowVisible,
    isFullscreen && styles.fullscreen,
    isFullHeight && styles.fullHeight,
    className
  )
  return (
    <section {...rest} className={css}>
      {children}
    </section>
  )
}

Box.defaultProps = {
  isMarginless: false,
  isOverflowVisible: false,
  isPaddingless: false,
  isFullscreen: false,
  isReducedPadding: false,
  isFullHeight: false,
}

export { Box }
