import type { FC, HTMLProps } from 'react'
import classnames from 'classnames'
import styles from './SocialProofPill.styles.scss'

interface Props extends HTMLProps<HTMLDivElement> {
  isVisible?: boolean
  isOverlayVisible?: boolean
}

const SocialProofPill: FC<Props> = ({ isVisible = true, isOverlayVisible, onClick, className }) => {
  const renderOverlay = () => (isOverlayVisible ? <div onClick={onClick} className={styles.overlay} /> : null)

  return (
    <>
      {isVisible ? (
        <div className={classnames(styles.root, className)} aria-hidden='true'>
          <span className={styles.initials}>JB</span>
          <span className={styles.text}>JB from Ottawa gave $99</span>
          {renderOverlay()}
        </div>
      ) : null}
    </>
  )
}

SocialProofPill.defaultProps = {
  isVisible: true,
  isOverlayVisible: false,
}

export { SocialProofPill }
