import type { FC, HTMLProps, PropsWithChildren } from 'react'
import classnames from 'classnames'
import styles from './Link.styles.scss'

type Props = PropsWithChildren<HTMLProps<HTMLDivElement>>

const Link: FC<Props> = ({ children, className }) => (
  <div className={classnames(styles.root, className)}>{children}</div>
)

export { Link }
