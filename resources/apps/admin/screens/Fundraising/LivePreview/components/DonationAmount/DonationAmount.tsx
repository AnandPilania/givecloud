import type { FC, HTMLProps } from 'react'
import classnames from 'classnames'
import { faMinus, faPlus } from '@fortawesome/free-solid-svg-icons'
import { Icon } from '../Icon'
import styles from './DonationAmount.styles.scss'

interface Props extends HTMLProps<HTMLDivElement> {
  isOverlayVisible?: boolean
  showIncrementIcons?: boolean
}

const DonationAmount: FC<Props> = ({ isOverlayVisible, onClick, value, showIncrementIcons = true, className }) => {
  const renderOverlay = () => (isOverlayVisible ? <div onClick={onClick} className={styles.overlay} /> : null)

  return (
    <div className={classnames(styles.root, className)}>
      {showIncrementIcons && <Icon size='small' icon={faMinus} className='mr-4' />}
      <div className={styles.text}>${value}</div>
      {showIncrementIcons && <Icon size='small' icon={faPlus} className='ml-4' />}
      {renderOverlay()}
    </div>
  )
}

DonationAmount.defaultProps = {
  isOverlayVisible: false,
  value: 45,
  showIncrementIcons: true,
}

export { DonationAmount }
