import type { HTMLProps, FC } from 'react'
import classnames from 'classnames'
import styles from './DropdownDivider.styles.scss'

interface Props extends Pick<HTMLProps<HTMLDivElement>, 'className'> {
  isMarginless?: boolean
}

const DropdownDivider: FC<Props> = ({ isMarginless, className }) => {
  const css = classnames(styles.root, isMarginless && styles.noMargin, className)
  return <div role='separator' className={css} />
}

export { DropdownDivider }
