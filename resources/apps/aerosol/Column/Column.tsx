import type { HTMLProps, ReactNode, FC } from 'react'
import type { ColumnSize } from '@/shared/constants/theme'
import classNames from 'classnames'
import styles from './Column.styles.scss'

interface Props extends HTMLProps<HTMLDivElement> {
  isPaddingless?: boolean
  columnWidth?: ColumnSize
  children?: ReactNode
}

const Column: FC<Props> = ({ children, isPaddingless, columnWidth = 'three', className, ...rest }) => {
  const css = classNames(styles.root, styles[columnWidth], isPaddingless && styles.isPaddingless, className)

  return (
    <div {...rest} className={css}>
      {children}
    </div>
  )
}

Column.defaultProps = {
  columnWidth: 'three',
  isPaddingless: false,
}

export { Column }
export { Props as ColumnProps }
