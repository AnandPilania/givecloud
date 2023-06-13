import type { FC, HTMLProps, PropsWithChildren } from 'react'
import type { IconDefinition } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import styles from './Input.styles.scss'

interface Props extends PropsWithChildren<HTMLProps<HTMLDivElement>> {
  icon: IconDefinition
}

const Input: FC<Props> = ({ children, icon }) => (
  <div className={styles.root}>
    <FontAwesomeIcon icon={icon} className={styles.icon} size='lg' />
    <span className={styles.text}>{children}</span>
  </div>
)

export { Input }
