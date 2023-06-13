import type { FC, HTMLProps, PropsWithChildren } from 'react'
import classnames from 'classnames'
import styles from './Text.styles.scss'

interface Props extends PropsWithChildren<HTMLProps<HTMLDivElement>> {
  isLarge?: boolean
}

const Text: FC<Props> = ({ children, isLarge, className }) => {
  const css = classnames(styles.root, isLarge && styles.large, className)

  return <div className={css}>{children}</div>
}

export { Text }
