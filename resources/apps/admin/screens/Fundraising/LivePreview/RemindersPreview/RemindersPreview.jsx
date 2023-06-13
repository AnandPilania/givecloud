import PropTypes from 'prop-types'
import { DonationPreview } from '@/screens/Fundraising/LivePreview/DonationPreview'
import { ExitConfirmationPreview } from './ExitConfirmationPreview'
import { StickyReminderPreview } from './StickyReminderPreview'

const RemindersPreview = ({
  isHovered,
  isExitConfirmationFocused,
  exitConfirmationOnClick,
  isStickyReminderFocused,
  isStickyReminderEnabled,
  stickyReminderOnClick,
  stickyReminderPosition,
}) => {
  const renderStickyReminderPreview = () =>
    isStickyReminderEnabled ? (
      <StickyReminderPreview
        isOverlayVisible={isHovered || isStickyReminderFocused}
        placement={stickyReminderPosition}
        onClick={stickyReminderOnClick}
      />
    ) : null

  return (
    <DonationPreview isEnabled={false}>
      <ExitConfirmationPreview
        isOverlayVisible={isHovered || isExitConfirmationFocused}
        onClick={exitConfirmationOnClick}
      />
      {renderStickyReminderPreview()}
    </DonationPreview>
  )
}

RemindersPreview.propTypes = {
  isHovered: PropTypes.bool,
  isExitConfirmationFocused: PropTypes.bool,
  exitConfirmationOnClick: PropTypes.func,
  stickyReminderOnClick: PropTypes.func,
  isStickyReminderFocused: PropTypes.bool,
  isStickyReminderEnabled: PropTypes.bool,
  stickyReminderPosition: PropTypes.oneOf(['bottom_left', 'bottom_center', 'bottom_right']),
}

export { RemindersPreview }
