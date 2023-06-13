import type { FC, HTMLProps } from 'react'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPlus, faChevronDown } from '@fortawesome/free-solid-svg-icons'
import { faInfoCircle } from '@fortawesome/pro-regular-svg-icons'
import styles from './DCCButton.styles.scss'

type Props = HTMLProps<HTMLDivElement>

const DCCButton: FC<Props> = ({ className }) => {
  return (
    <div className={classnames(styles.root, className)}>
      <FontAwesomeIcon icon={faPlus} className={styles.addIcon} />
      <div className={styles.text}>
        Add{' '}
        <span className={styles.dropdown}>
          $9.99
          <FontAwesomeIcon icon={faChevronDown} size='xs' className={styles.chevron} />
        </span>
        <span>to help cover our costs.</span>
        <FontAwesomeIcon icon={faInfoCircle} size='lg' className={styles.infoIcon} />
      </div>
    </div>
  )
}

export { DCCButton }
