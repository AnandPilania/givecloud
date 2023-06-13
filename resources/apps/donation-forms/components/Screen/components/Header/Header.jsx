import { memo, useCallback } from 'react'
import { useRecoilState, useRecoilValue } from 'recoil'
import { useHistory, useLocation } from 'react-router-dom'
import { useWindowSize } from 'react-use'
import { SCREEN_LARGE } from '@/constants/breakpointConstants'
import CloseButton from '@/components/CloseButton/CloseButton'
import ExitConfirmModal from './components/ExitConfirmModal/ExitConfirmModal'
import LogoFlipper from '@/components/LogoFlipper/LogoFlipper'
import SelectDropdown from '@/components/SelectDropdown/SelectDropdown'
import configState from '@/atoms/config'
import localeState from '@/atoms/locale'
import checkoutScreenState from '@/atoms/checkoutScreen'
import contributionState from '@/atoms/contribution'
import screenHeaderState from '@/atoms/screenHeader'
import showFAQState from '@/atoms/showFAQ'
import showPrivacyAndLegalState from '@/atoms/showPrivacyAndLegal'
import useCloseForm from '@/hooks/useCloseForm'
import { CHOOSE_PAYMENT_METHOD, CHECKOUT } from '@/constants/pathConstants'
import BackIcon from './images/Back.svg?react'
import styles from './Header.scss'

const Header = () => {
  const config = useRecoilValue(configState)
  const checkoutScreen = useRecoilValue(checkoutScreenState)
  const contribution = useRecoilValue(contributionState)

  const [locale, setLocale] = useRecoilState(localeState)
  const [showFAQ, setShowFAQ] = useRecoilState(showFAQState)
  const [showPrivacyAndLegal, setShowPrivacyAndLegal] = useRecoilState(showPrivacyAndLegalState)

  const { width: windowWidth } = useWindowSize()
  const { isConfirmModalOpen, setIsConfirmModalOpen, closeFundraisingForm } = useCloseForm()

  const history = useHistory()
  const location = useLocation()

  let showLogo = !!config.logo_url
  let { showHeader, showBackButton, showCloseButton, showLocaleSwitcher } = useRecoilValue(screenHeaderState)

  if (showFAQ || showPrivacyAndLegal) {
    showBackButton = false
  }

  const handleOnCloseButtonClick = () => {
    if (showFAQ) {
      return setShowFAQ(false)
    } else if (showPrivacyAndLegal) {
      return setShowPrivacyAndLegal(false)
    } else if (contribution) {
      closeFundraisingForm()
    } else {
      setIsConfirmModalOpen(true)
    }
  }

  const handleOnBackButtonClick = useCallback(() => {
    if (location.pathname === CHECKOUT && checkoutScreen.active !== 'pay_with_credit_card') {
      window.G_navigateTo('pay_with_credit_card', 'POP')
    } else {
      history.goBack()
    }
  }, [checkoutScreen, location, history])

  const handleOnLocaleChange = (e) => {
    setLocale(e.target.value)
  }

  const localeOptions = [
    { label: 'EN', value: 'en-US' },
    { label: 'FR', value: 'fr-CA' },
    { label: 'ES', value: 'es-MX' },
  ]

  const isLargeScreen = windowWidth >= SCREEN_LARGE
  if (config.widget_type === 'inline_embed' || (config.layout === 'standard' && isLargeScreen)) {
    showCloseButton = false
    showLogo = false

    if (showFAQ || showPrivacyAndLegal || location.pathname === CHOOSE_PAYMENT_METHOD) {
      showHeader = false
    }
  }

  if (history?.length < 2) {
    showBackButton = false
  }

  // temporarily disable locale switching
  showLocaleSwitcher = false

  if (!showHeader) {
    return null
  }

  return (
    <div className={styles.root}>
      <div className={styles.content}>
        <div className={styles.headerButton}>
          {showBackButton && (
            <button className={styles.backButton} onClick={handleOnBackButtonClick}>
              <BackIcon />
            </button>
          )}

          {showLocaleSwitcher && (
            <SelectDropdown
              clean={true}
              className={styles.localeDropdown}
              defaultValue={locale}
              onChange={handleOnLocaleChange}
              options={localeOptions}
            />
          )}
        </div>

        {showLogo && <LogoFlipper href={config.campaign_url} />}

        <div className={styles.headerButton}>
          {showCloseButton && <CloseButton onClick={handleOnCloseButtonClick} />}
        </div>

        <ExitConfirmModal
          isOpen={isConfirmModalOpen}
          dismiss={() => setIsConfirmModalOpen(false)}
          close={closeFundraisingForm}
        />
      </div>

      <div className={styles.background}>
        <div className={styles.solidBg} />
        <div className={styles.gradientBg} />
      </div>
    </div>
  )
}

export default memo(Header)
