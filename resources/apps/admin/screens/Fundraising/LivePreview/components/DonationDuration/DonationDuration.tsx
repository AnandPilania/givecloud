import type { FC, HTMLProps } from 'react'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheck } from '@fortawesome/pro-regular-svg-icons'
import { Pill } from '../Pill'
import styles from './DonationDuration.styles.scss'

interface Props extends HTMLProps<HTMLDivElement> {
  type?: string
  isOverlayVisible?: boolean
}

const DonationDuration: FC<Props> = ({ className, type = 'today_only|monthly', isOverlayVisible, onClick }) => {
  const isMonthlyDefault = type === 'monthly|today_only' ? true : false
  const renderSinglePillText = () => (type === 'today_only' ? 'Today Only' : 'Monthly')
  const renderOverlay = () => (isOverlayVisible ? <div onClick={onClick} className={styles.overlay} /> : null)

  const renderContent = () => {
    if (type === 'today_only|monthly' || type === 'monthly|today_only') {
      return (
        <>
          <Pill isInverted={isMonthlyDefault} className='mr-2'>
            {!isMonthlyDefault ? <FontAwesomeIcon icon={faCheck} className='mr-1 mb-0.5' /> : null}
            Today Only
          </Pill>
          <Pill isInverted={!isMonthlyDefault}>
            {isMonthlyDefault ? <FontAwesomeIcon icon={faCheck} className='mr-1 mb-0.5' /> : null}
            Monthly
          </Pill>
        </>
      )
    }

    if (type === 'today_only' || type === 'monthly') {
      return (
        <Pill>
          <FontAwesomeIcon icon={faCheck} className='mr-1 mb-0.5' />
          {renderSinglePillText()}
        </Pill>
      )
    }
  }

  return (
    <div className={classnames(styles.root, className)}>
      {renderContent()}
      {renderOverlay()}
    </div>
  )
}

DonationDuration.defaultProps = {
  type: 'today_only|monthly',
  isOverlayVisible: false,
}

export { DonationDuration }
