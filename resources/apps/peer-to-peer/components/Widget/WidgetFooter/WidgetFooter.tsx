import type { FC, HTMLProps, PropsWithChildren } from 'react'
import { DialogFooter } from '@/aerosol'
import classNames from 'classnames'
import styles from './WidgetFooter.styles.scss'

type Props = PropsWithChildren & Pick<HTMLProps<HTMLDivElement>, 'className'>

const WidgetFooter: FC<Props> = ({ children, className }) => {
  return <DialogFooter className={classNames(styles.root, className)}>{children}</DialogFooter>
}

export { WidgetFooter }
