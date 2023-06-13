import { useTailwindBreakpoints } from '@/shared/hooks'
import type { FC, PropsWithChildren } from 'react'
import styles from './LayoutFooter.styles.scss'

const LayoutFooter: FC<PropsWithChildren> = ({ children }) => {
  const { large } = useTailwindBreakpoints()

  if (large.lessThan) return null
  return <div className={styles.root}>{children}</div>
}

export { LayoutFooter }
