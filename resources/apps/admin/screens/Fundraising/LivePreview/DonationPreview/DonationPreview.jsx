import PropTypes from 'prop-types'
import { StandardDonationPreview } from './StandardDonationPreview'
import { TilesDonationPreview } from './TilesDonationPreview'
import { useFundraisingFormState } from '../../useFundraisingFormState'

const DonationPreview = ({
  socialProofOnClick,
  isSocialProofFocused,
  isSocialProofVisible,
  todayAndMonthlyOnClick,
  isTodayAndMonthlyFocused,
  defaultAmountOnClick,
  isDefaultAmountFocused,
  transparencyPromiseOnClick,
  isTransparencyPromiseFocused,
  isHovered,
  isEnabled,
  children = null,
}) => {
  const { templateValue } = useFundraisingFormState()

  return templateValue.template?.type === 'amount_tiles' ? (
    <TilesDonationPreview
      isHovered={isHovered}
      isEnabled={isEnabled}
      isSocialProofFocused={isSocialProofFocused}
      socialProofOnClick={socialProofOnClick}
      isSocialProofVisible={isSocialProofVisible}
      isTodayAndMonthlyFocused={isTodayAndMonthlyFocused}
      todayAndMonthlyOnClick={todayAndMonthlyOnClick}
      isTransparencyPromiseFocused={isTransparencyPromiseFocused}
      transparencyPromiseOnClick={transparencyPromiseOnClick}
      isDefaultAmountFocused={isDefaultAmountFocused}
      defaultAmountOnClick={defaultAmountOnClick}
    >
      {children}
    </TilesDonationPreview>
  ) : (
    <StandardDonationPreview
      isEnabled={isEnabled}
      isHovered={isHovered}
      socialProofOnClick={socialProofOnClick}
      isSocialProofFocused={isSocialProofFocused}
      isSocialProofVisible={isSocialProofVisible}
      isTodayAndMonthlyFocused={isTodayAndMonthlyFocused}
      todayAndMonthlyOnClick={todayAndMonthlyOnClick}
      defaultAmountOnClick={defaultAmountOnClick}
      isDefaultAmountFocused={isDefaultAmountFocused}
      transparencyPromiseOnClick={transparencyPromiseOnClick}
      isTransparencyPromiseFocused={isTransparencyPromiseFocused}
    >
      {children}
    </StandardDonationPreview>
  )
}

DonationPreview.propTypes = {
  socialProofOnClick: PropTypes.func,
  isSocialProofFocused: PropTypes.bool,
  isSocialProofVisible: PropTypes.bool,
  todayAndMonthlyOnClick: PropTypes.func,
  isTodayAndMonthlyFocused: PropTypes.bool,
  defaultAmountOnClick: PropTypes.func,
  isDefaultAmountFocused: PropTypes.bool,
  transparencyPromiseOnClick: PropTypes.func,
  isTransparencyPromiseFocused: PropTypes.bool,
  isHovered: PropTypes.bool,
  isEnabled: PropTypes.bool,
}

DonationPreview.defaultProps = {
  isEnabled: true,
  isHovered: false,
  isSocialProofFocused: false,
  isSocialProofVisible: true,
  isTransparencyPromiseFocused: false,
}

export { DonationPreview }
