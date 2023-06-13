import type { FC, HTMLProps, PropsWithChildren } from 'react'
import classnames from 'classnames'
import styles from './Button.styles.scss'

interface Props extends PropsWithChildren<HTMLProps<HTMLDivElement>> {
  isOutlined?: boolean
}

const Button: FC<Props> = ({ children, className, isOutlined }) => {
  const css = classnames(styles.root, isOutlined && styles.isOutlined, className)

  return <div className={css}>{children}</div>
}

export { Button }
