import type { FC, HTMLProps, PropsWithChildren } from 'react'
import type { IconDefinition } from '@fortawesome/pro-regular-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import classNames from 'classnames'
import styles from './InfoBox.styles.scss'

interface Props extends PropsWithChildren<HTMLProps<HTMLDivElement>> {
  icon?: IconDefinition
  isMarginless?: boolean
}

const InfoBox: FC<Props> = ({ icon, children, isMarginless, className }) => {
  const css = classNames(styles.root, className, !isMarginless && styles.margin)

  const renderIcon = () => (icon ? <FontAwesomeIcon icon={icon} size='lg' aria-hidden={true} className='mr-5' /> : null)

  return (
    <div className={css}>
      {renderIcon()}
      {children}
    </div>
  )
}

export { InfoBox }
