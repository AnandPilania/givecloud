import type { FC, HTMLProps, PropsWithChildren } from 'react'
import classnames from 'classnames'
import styles from './Skeleton.styles.scss'

interface Props extends PropsWithChildren<HTMLProps<HTMLDivElement>> {
  isOverlayVisible?: boolean
}

const Skeleton: FC<Props> = ({ isOverlayVisible, onClick, className }) => {
  const renderOverlay = () => (isOverlayVisible ? <div onClick={onClick} className={styles.overlay} /> : null)

  return (
    <div className={classnames(styles.root, className)}>
      <div className={classnames(styles.line, styles.medium, 'mb-2')} />
      <div className={classnames(styles.line, styles.long, 'mb-2')} />
      <div className={classnames(styles.line, styles.short)} />
      {renderOverlay()}
    </div>
  )
}

Skeleton.defaultProps = {
  isOverlayVisible: false,
}

export { Skeleton }
