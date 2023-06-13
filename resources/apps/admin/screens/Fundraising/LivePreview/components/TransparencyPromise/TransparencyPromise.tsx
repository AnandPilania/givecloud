import type { FC, HTMLProps } from 'react'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faHeart } from '@fortawesome/free-solid-svg-icons'
import styles from './TransparencyPromise.styles.scss'

interface Props extends HTMLProps<HTMLDivElement> {
  isVisible?: boolean
  isOverlayVisible?: boolean
  type?: string
}

const TransparencyPromise: FC<Props> = ({ isVisible = true, isOverlayVisible, onClick, className, type }) => {
  const renderOverlay = () => (isOverlayVisible ? <div onClick={onClick} className={styles.overlay} /> : null)

  if (type === 'statement') {
    return (
      <div className={classnames(styles.root, isVisible ? 'visible' : 'invisible', className)}>
        <div className={styles.header}>
          Impact Promise
          <FontAwesomeIcon icon={faHeart} size='sm' />
        </div>
        <div className={styles.content}>
          <div className={classnames(styles.skeleton, styles.long, 'mb-1')} />
          <div className={classnames(styles.skeleton, styles.short)} />
        </div>
        {renderOverlay()}
      </div>
    )
  }
  return (
    <div className={classnames(styles.root, isVisible ? 'visible' : 'invisible', className)}>
      <div className={styles.header}>
        Impact Promise <FontAwesomeIcon icon={faHeart} size='sm' />
      </div>
      <div className={styles.content}>
        <div className={classnames(styles.text, 'mb-1')}>
          $99
          <div className={classnames(styles.skeleton, styles.long)} />
        </div>
        <div className={styles.text}>
          $99
          <div className={classnames(styles.skeleton, styles.short)} />
        </div>
      </div>
      {renderOverlay()}
    </div>
  )
}

TransparencyPromise.defaultProps = {
  isVisible: true,
  isOverlayVisible: false,
}

export { TransparencyPromise }
