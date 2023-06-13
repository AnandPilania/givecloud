import { useTailwindBreakpoints } from '@/shared/hooks'
import type { FC, PropsWithChildren } from 'react'
import styles from './LayoutContent.styles.scss'

const LayoutContent: FC<PropsWithChildren> = ({ children }) => {
  const { large } = useTailwindBreakpoints()
  if (large.lessThan) return null
  return <div className={styles.root}>{children}</div>
}

export { LayoutContent }
