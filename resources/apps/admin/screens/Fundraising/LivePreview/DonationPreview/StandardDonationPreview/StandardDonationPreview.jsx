import PropTypes from 'prop-types'
import { Box, Button, DonationAmount, DonationDuration, SocialProofPill, TransparencyPromise } from '../../components'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './StandardDonationPreview.scss'

const StandardDonationPreview = ({
  socialProofOnClick,
  isSocialProofFocused,
  isSocialProofVisible,
  isTodayAndMonthlyFocused,
  todayAndMonthlyOnClick,
  defaultAmountOnClick,
  isDefaultAmountFocused,
  transparencyPromiseOnClick,
  isTransparencyPromiseFocused,
  isHovered,
  children,
  isEnabled,
}) => {
  const { brandingValue, transparencyPromiseValue, socialProofValue, defaultAmountValue, todayAndMonthlyValue } =
    useFundraisingFormState()
  const css = isEnabled ? 'opacity-100' : 'opacity-30'

  const renderLogo = () =>
    brandingValue.brandingLogo.full ? (
      <img src={brandingValue.brandingLogo.full} alt='' className={styles.logo} />
    ) : null

  const renderSocialProof = () =>
    isSocialProofVisible ? (
      <SocialProofPill
        isVisible={socialProofValue.socialProofEnabled}
        className={styles.socialProofPill}
        isOverlayVisible={isHovered || isSocialProofFocused}
        onClick={socialProofOnClick}
      />
    ) : null

  return (
    <div className='relative'>
      <Box className={css}>
        {renderSocialProof()}
        {renderLogo()}
        <DonationAmount
          value={defaultAmountValue.defaultAmountValue}
          isOverlayVisible={isHovered || isDefaultAmountFocused}
          onClick={defaultAmountOnClick}
          className='mt-36'
        />
        <DonationDuration
          type={todayAndMonthlyValue.billingPeriods}
          onClick={todayAndMonthlyOnClick}
          isOverlayVisible={isHovered || isTodayAndMonthlyFocused}
          className='mt-6'
        />
        <TransparencyPromise
          type={transparencyPromiseValue.transparencyPromiseType}
          className='mt-32 mb-3'
          isVisible={transparencyPromiseValue.transparencyPromiseEnabled}
          isOverlayVisible={isHovered || isTransparencyPromiseFocused}
          onClick={transparencyPromiseOnClick}
        />
        <Button>Donate</Button>
      </Box>
      {children}
    </div>
  )
}

StandardDonationPreview.propTypes = {
  isEnabled: PropTypes.bool,
  socialProofOnClick: PropTypes.func,
  isSocialProofFocused: PropTypes.bool,
  isSocialProofVisible: PropTypes.bool,
  defaultAmountOnClick: PropTypes.func,
  isDefaultAmountFocused: PropTypes.bool,
  todayAndMonthlyOnClick: PropTypes.func,
  isTodayAndMonthlyFocused: PropTypes.bool,
  transparencyPromiseOnClick: PropTypes.func,
  isTransparencyPromiseFocused: PropTypes.bool,
  isHovered: PropTypes.bool,
}

StandardDonationPreview.propsTypes = {
  isSocialProofVisible: true,
}

export { StandardDonationPreview }
