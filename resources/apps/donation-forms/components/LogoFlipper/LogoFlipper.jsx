import { useRecoilValue } from 'recoil'
import { useLocation } from 'react-router-dom'
import classnames from 'classnames'
import PropTypes from 'prop-types'
import configState from '@/atoms/config'
import variantState from '@/atoms/variant'
import styles from './LogoFlipper.scss'
import { MONTHLY_UPSELL } from '@/constants/pathConstants'

const LogoFlipper = ({ className, href }) => {
  const config = useRecoilValue(configState)
  const variant = useRecoilValue(variantState)
  const location = useLocation()

  const frontLogoUrl = config.logo_url
  const reverseLogoUrl = config.monthly_logo_url
  const shouldShowReverseLogo = variant.billing_period === 'monthly' || location.pathname === MONTHLY_UPSELL
  const showReverseLogo = shouldShowReverseLogo && reverseLogoUrl

  if (!frontLogoUrl) {
    return null
  }

  const renderLogo = () => (
    <>
      <img className={styles.frontLogo} src={frontLogoUrl} />
      {reverseLogoUrl && <img className={styles.reverseLogo} src={reverseLogoUrl} />}
    </>
  )

  if (href) {
    return (
      <a
        className={classnames(styles.root, showReverseLogo && styles.showReverseLogo, className)}
        href={href}
        rel='noreferrer'
        target='_blank'
      >
        {renderLogo()}
      </a>
    )
  }

  return (
    <div className={classnames(styles.root, showReverseLogo && styles.showReverseLogo, className)}>{renderLogo()}</div>
  )
}

LogoFlipper.propTypes = {
  className: PropTypes.string,
  href: PropTypes.string,
}

export default LogoFlipper
