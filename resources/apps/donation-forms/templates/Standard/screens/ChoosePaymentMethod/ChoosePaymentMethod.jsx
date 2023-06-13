import { useState } from 'react'
import { useRecoilState, useRecoilValue } from 'recoil'
import { useWindowSize } from 'react-use'
import classnames from 'classnames'
import Screen from '@/components/Screen/Screen'
import AmountSelector from './components/AmountSelector/AmountSelector'
import FooterNavigation from '@/components/FooterNavigation/FooterNavigation'
import PaymentMethodSelector from '@/components/PaymentMethodSelector/PaymentMethodSelector'
import TransparencyPromise from '@/components/TransparencyPromise/TransparencyPromise'
import AmountThermometer from '@/components/AmountThermometer/AmountThermometer'
import useLocalization from '@/hooks/useLocalization'
import configState from '@/atoms/config'
import Button from '@/components/Button/Button'
import BadgePreviewContainer from '@/components/BadgePreviewContainer/BadgePreviewContainer'
import { SCREEN_LARGE } from '@/constants/breakpointConstants'
import formInputState from '@/atoms/formInput'
import styles from './ChoosePaymentMethod.scss'

const ChoosePaymentMethod = () => {
  const t = useLocalization('screens.choose_payment_method.payment_method_selector')
  const { width: windowWidth } = useWindowSize()
  const [formInput] = useRecoilState(formInputState)

  const config = useRecoilValue(configState)
  const {
    layout,
    peer_to_peer: { campaign },
  } = config
  const [showPaymentMethodSelector, setShowPaymentMethodSelector] = useState(false)

  const openPaymentMethodSelector = () => setShowPaymentMethodSelector(true)
  const closePaymentMethodSelector = () => setShowPaymentMethodSelector(false)

  const isLargeScreen = windowWidth >= SCREEN_LARGE
  const isStandardLayout = layout === 'standard'
  const isLargeScreenStandardLayout = isStandardLayout && isLargeScreen

  const renderThermometer = () => {
    if (campaign) {
      const { title, amount_raised, goal_amount, social_avatar, avatar_name, currency_code } = campaign

      const thumbnail =
        avatar_name === 'custom' && social_avatar
          ? social_avatar
          : `https://cdn.givecloud.co/s/assets/avatars/${avatar_name}.svg`

      return (
        <AmountThermometer
          layout={layout}
          thumbnail={thumbnail}
          title={title}
          amountRaised={amount_raised}
          goalAmount={goal_amount}
          currentAmount={formInput.item.amt}
          currencyCode={currency_code}
        />
      )
    }
    return null
  }

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
        {renderThermometer()}
        <BadgePreviewContainer>
          <AmountSelector />
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
