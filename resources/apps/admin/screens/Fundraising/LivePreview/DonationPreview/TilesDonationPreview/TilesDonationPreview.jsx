import PropTypes from 'prop-types'
import { Box, Button, DonationAmount, DonationDuration, SocialProofPill, TransparencyPromise } from '../../components'
import { DonationTiles } from './DonationTiles'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './TilesDonationPreview.scss'

const TilesDonationPreview = ({
  socialProofOnClick,
  isSocialProofFocused,
  isSocialProofVisible,
  isTodayAndMonthlyFocused,
  todayAndMonthlyOnClick,
  transparencyPromiseOnClick,
  isTransparencyPromiseFocused,
  defaultAmountOnClick,
  isDefaultAmountFocused,
  isHovered,
  isEnabled,
  children,
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
          value={defaultAmountValue.defaultAmountValues[0].value}
          showIncrementIcons={false}
          className='mt-20'
        />
        <DonationDuration
          type={todayAndMonthlyValue.billingPeriods}
          onClick={todayAndMonthlyOnClick}
          isOverlayVisible={isHovered || isTodayAndMonthlyFocused}
          className='mt-4 mb-8'
        />
        <DonationTiles
          isOverlayVisible={isHovered || isDefaultAmountFocused}
          onClick={defaultAmountOnClick}
          type={defaultAmountValue.defaultAmountType}
          values={defaultAmountValue.defaultAmountValues.map(({ value }) => value)}
        />
        <TransparencyPromise
          type={transparencyPromiseValue.transparencyPromiseType}
          className='mt-8 mb-3'
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

TilesDonationPreview.propTypes = {
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

export { TilesDonationPreview }
