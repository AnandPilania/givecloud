import { useWindowSize } from 'react-use'
import { useRecoilValue } from 'recoil'
import classnames from 'classnames'
import Givecloud from 'givecloud'
import CurrencySelector from '@/components/CurrencySelector/CurrencySelector'
import FooterLinks from './FooterLinks/FooterLinks'
import FrequentlyAskedQuestions from '@/screens/FAQAndPrivacyLegal/FrequentlyAskedQuestions/FrequentlyAskedQuestions'
import PrivacyAndLegal from '@/screens/FAQAndPrivacyLegal/PrivacyAndLegal/PrivacyAndLegal'
import configState from '@/atoms/config'
import styles from './FooterNavigation.scss'
import { SCREEN_LARGE } from '@/constants/breakpointConstants'
import useFooterLinks from '@/hooks/useFooterLinks'

const FooterNavigation = () => {
  const { width: windowWidth } = useWindowSize()
  const { footerLinks } = useFooterLinks()

  const config = useRecoilValue(configState)
  const currencies = Givecloud.config.currencies

  const showCallToAction = config.navigation.footer_cta.enabled && config.navigation.footer_cta.label
  const showCurrencySelector = !showCallToAction

  const isLargeScreenStandardLayout = config.layout === 'standard' && windowWidth >= SCREEN_LARGE

  return (
    <div
      className={classnames(
        styles.root,
        isLargeScreenStandardLayout && !showCallToAction && currencies.length < 2 && styles.standardLayout
      )}
    >
      {showCallToAction && (
        <a
          className={styles.callToAction}
          href={config.navigation.footer_cta.link}
          target='_blank'
          rel='noopener noreferrer'
        >
          {config.navigation.footer_cta.label}
        </a>
      )}

      {showCurrencySelector && <CurrencySelector className={styles.currencySelector} clean />}
      {isLargeScreenStandardLayout ? null : <FooterLinks links={footerLinks} className={styles.footerLinks} />}

      <FrequentlyAskedQuestions />
      <PrivacyAndLegal />
    </div>
  )
}

export default FooterNavigation
