import { useState } from 'react'
import { useRecoilValue } from 'recoil'
import classnames from 'classnames'
import BadgePreviewContainer from '@/components/BadgePreviewContainer/BadgePreviewContainer'
import Button from '@/components/Button/Button'
import FooterNavigation from '@/components/FooterNavigation/FooterNavigation'
import PaymentMethodSelector from '@/components/PaymentMethodSelector/PaymentMethodSelector'
import Screen from '@/components/Screen/Screen'
import TransparencyPromise from '@/components/TransparencyPromise/TransparencyPromise'
import AmountSelector from './components/AmountSelector/AmountSelector'
import configState from '@/atoms/config'
import useLocalization from '@/hooks/useLocalization'
import { useWindowSize } from 'react-use'
import { SCREEN_LARGE } from '@/constants/breakpointConstants'
import styles from './ChoosePaymentMethod.scss'

const ChoosePaymentMethod = () => {
  const t = useLocalization('screens.choose_payment_method.payment_method_selector')
  const config = useRecoilValue(configState)
  const { width: windowWidth } = useWindowSize()

  const [showPaymentMethodSelector, setShowPaymentMethodSelector] = useState(false)

  const openPaymentMethodSelector = () => setShowPaymentMethodSelector(true)
  const closePaymentMethodSelector = () => setShowPaymentMethodSelector(false)

  const isLargeScreen = windowWidth >= SCREEN_LARGE
  const isStandardLayout = config.layout === 'standard'
  const isLargeScreenStandardLayout = isStandardLayout && isLargeScreen

  return (
    <Screen
      className={classnames(
        styles.root,
        config.widget_type === 'inline_embed' && styles.inlineWidget,
        isLargeScreenStandardLayout && styles.standardLayout
      )}
      showBackButton={config.layout === 'standard'}
      showLocaleSwitcher={true}
    >
      <div className={styles.content}>
        <BadgePreviewContainer className={styles.badgePreviewContainer}>
          <AmountSelector small />
        </BadgePreviewContainer>

        <div className={styles.components}>
          {config.transparency_promise.enabled && (
            <div className={styles.transparencyPromiseContainer}>
              <TransparencyPromise />
            </div>
          )}

          <Button className={styles.donateBtn} onClick={openPaymentMethodSelector}>
            {t('donate')}
          </Button>

          <PaymentMethodSelector open={showPaymentMethodSelector} onClose={closePaymentMethodSelector} />
          <FooterNavigation />
        </div>
      </div>
    </Screen>
  )
}

export default ChoosePaymentMethod
