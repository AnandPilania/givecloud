import type { FC, HTMLProps, PropsWithChildren } from 'react'
import classnames from 'classnames'
import styles from './Pill.styles.scss'

interface Props extends PropsWithChildren<HTMLProps<HTMLDivElement>> {
  isInverted?: boolean
}

const Pill: FC<Props> = ({ children, isInverted, className }) => {
  const css = classnames(styles.root, isInverted && styles.inverted, className)

  return <div className={css}>{children}</div>
}

Pill.defaultProps = {
  isInverted: false,
}

export { Pill }
