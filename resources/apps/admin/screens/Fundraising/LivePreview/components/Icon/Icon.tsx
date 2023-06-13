import type { FC, HTMLProps } from 'react'
import type { IconDefinition } from '@fortawesome/fontawesome-svg-core'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import styles from './Icon.styles.scss'

type Size = 'small' | 'default'
interface Props extends Omit<HTMLProps<HTMLDivElement>, 'size'> {
  icon: IconDefinition
  size?: Size
}
const Icon: FC<Props> = ({ icon, size = 'default', className }) => {
  const css = classnames(styles.root, styles[size], className)

  return (
    <div className={css}>
      <FontAwesomeIcon icon={icon} className={classnames(size === 'small' ? styles.smallFont : styles.defaultFont)} />
    </div>
  )
}

export { Icon }
