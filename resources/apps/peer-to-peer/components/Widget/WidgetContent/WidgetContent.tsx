import type { FC, HTMLProps, PropsWithChildren } from 'react'
import classNames from 'classnames'
import styles from './WidgetContent.styles.scss'

type Props = PropsWithChildren & Pick<HTMLProps<HTMLDivElement>, 'className'>

const WidgetContent: FC<Props> = ({ children, className }) => {
  return <div className={classNames(styles.root, className)}>{children}</div>
}

export { WidgetContent }
