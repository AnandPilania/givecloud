import type { FC, ReactNode } from 'react'
import { PRIMARY, CUSTOM_THEME as CUSTOM } from '@/shared/constants/theme'
import classNames from 'classnames'
import styles from './Card.styles.scss'

interface Props {
  children: ReactNode
  isMarginless?: boolean
}

const Card: FC<Props> = ({ children, isMarginless }) => {
  const css = classNames(styles.root, !isMarginless && styles.margin)

  return <div className={css}>{children}</div>
}

export { Card }
