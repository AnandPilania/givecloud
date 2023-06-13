import type { FC, PropsWithChildren } from 'react'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './LayoutHeader.styles.scss'

const LayoutHeader: FC<PropsWithChildren> = ({ children }) => {
  const { large } = useTailwindBreakpoints()

  if (large.lessThan) return null
  return <div className={styles.root}>{children}</div>
}

export { LayoutHeader }
