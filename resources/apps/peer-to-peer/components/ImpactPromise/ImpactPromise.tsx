import type { FC, ReactNode } from 'react'
import styles from './ImpactPromise.styles.scss'

interface Props {
  children: ReactNode
}

const ImpactPromise: FC<Props> = ({ children }) => {
  return <div className={styles.root}>{children}</div>
}

export { ImpactPromise }
