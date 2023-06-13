import { memo } from 'react'
import styles from '@/components/GCLogo/GCLogo.scss'

const GCLogo = () => (
  <img
    className={styles.root}
    src='https://cdn.givecloud.co/static/etc/givecloud-live-events-logo.svg'
  />
)

export default memo(GCLogo)
