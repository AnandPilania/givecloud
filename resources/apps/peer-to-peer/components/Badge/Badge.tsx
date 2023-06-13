import type { FC, HTMLProps } from 'react'
import type { IconProp } from '@fortawesome/fontawesome-svg-core'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { motion } from 'framer-motion'
import { Text } from '@/aerosol'
import styles from './Badge.styles.scss'

interface Icons {
  [percentile: number]: IconProp
}

interface Props extends HTMLProps<HTMLDivElement> {
  percentage: number
  icons?: Icons
}

const Badge: FC<Props> = ({ percentage, icons, className }) => {
  const renderIcon = () =>
    icons ? <FontAwesomeIcon icon={icons[percentage]} className={styles.icon} aria-hidden={true} /> : null

  return (
    <motion.div
      key={percentage}
      initial={{ rotateX: 180 }}
      animate={{ rotateX: 0 }}
      exit={{ rotateX: 180 }}
      transition={{ duration: 0.4, ease: 'easeInOut' }}
      className={classNames(styles.root, className)}
    >
      {renderIcon()}
      <Text isMarginless>
        Top {percentage}% <span className={styles.fontNormal}>of Fundraisers</span>
      </Text>
      <div className={styles.shineEffect} />
    </motion.div>
  )
}

export { Badge }
