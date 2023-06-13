import PropTypes from 'prop-types'
import classnames from 'classnames'
import { faXmark } from '@fortawesome/free-solid-svg-icons'
import { Button, Icon } from '../../components'
import styles from './StickyReminderPreview.scss'

const StickyReminderPreview = ({ placement, isOverlayVisible, onClick }) => {
  const css = classnames(styles.root, styles[placement])

  return (
    <div className={css}>
      <div className={styles.header}>
        <div className={classnames(styles.skeleton, isOverlayVisible && styles.overlay)} onClick={onClick} />
        <Icon icon={faXmark} size='small' className={styles.icon} />
      </div>
      <div className='flex'>
        <Button isOutlined className='mr-2'>
          Finish
        </Button>
        <Button>No Thank You</Button>
      </div>
    </div>
  )
}

StickyReminderPreview.propTypes = {
  placement: PropTypes.oneOf(['bottom_left', 'bottom_center', 'bottom_right']),
  isOverlayVisible: PropTypes.bool,
  onClick: PropTypes.func,
}

StickyReminderPreview.defaultProps = {
  placement: 'center',
  isOverlayVisible: false,
}

export { StickyReminderPreview }
