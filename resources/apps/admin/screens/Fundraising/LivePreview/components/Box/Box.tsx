import type { FC, HTMLProps, PropsWithChildren } from 'react'
import classnames from 'classnames'
import styles from './Box.styles.scss'

type Size = 'small' | 'large'
interface Props extends PropsWithChildren<Omit<HTMLProps<HTMLDivElement>, 'size'>> {
  size?: Size
}

const Box: FC<Props> = ({ children, size = 'large', className }) => {
  const css = classnames(styles.root, styles[size], className)

  return (
    <div className={css} aria-hidden='true'>
      {children}
    </div>
  )
}

export { Box }
