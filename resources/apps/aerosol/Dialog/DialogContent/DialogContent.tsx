import type { FC, HTMLProps, ReactNode } from 'react'
import classNames from 'classnames'
import styles from './DialogContent.styles.scss'

interface Props extends Pick<HTMLProps<HTMLDivElement>, 'className'> {
  children: ReactNode
}

const DialogContent: FC<Props> = ({ children, className }) => {
  return <div className={classNames(styles.root, className)}>{children}</div>
}

export { DialogContent }
