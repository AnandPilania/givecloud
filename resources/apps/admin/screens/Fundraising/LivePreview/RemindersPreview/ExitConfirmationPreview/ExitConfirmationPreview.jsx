import PropTypes from 'prop-types'
import { faXmark } from '@fortawesome/free-solid-svg-icons'
import { Box, Button, Icon, Skeleton } from '../../components'
import styles from './ExitConfirmationPreview.scss'

const ExitConfirmationPreview = ({ isOverlayVisible, onClick }) => {
  return (
    <Box size='small' className={styles.root}>
      <Icon icon={faXmark} size='small' className={styles.closeIcon} />
      <Skeleton className='mb-8 mt-12' isOverlayVisible={isOverlayVisible} onClick={onClick} />
      <Button>Finish My Donation</Button>
      <Button isOutlined className='mt-2 mb-4'>
        No Thank You
      </Button>
    </Box>
  )
}

ExitConfirmationPreview.propTypes = {
  isOverlayVisible: PropTypes.bool,
  onClick: PropTypes.func,
}

ExitConfirmationPreview.defaultProps = {
  isOverlayVisible: false,
}

export { ExitConfirmationPreview }
