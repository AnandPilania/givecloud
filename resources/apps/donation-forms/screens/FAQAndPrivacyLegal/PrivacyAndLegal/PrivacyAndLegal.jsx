import { memo } from 'react'
import { useRecoilState, useRecoilValue } from 'recoil'
import { useWindowSize } from 'react-use'
import classnames from 'classnames'
import { AnimatePresence, motion } from 'framer-motion'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight, faShieldCheck } from '@fortawesome/pro-light-svg-icons'
import { faCheckCircle } from '@fortawesome/free-solid-svg-icons'
import useLocalization from '@/hooks/useLocalization'
import Portal from '@/components/Portal/Portal'
import Header from '@/components/Screen/components/Header/Header'
import Translation from '@/components/Translation/Translation'
import configState from '@/atoms/config'
import showPrivacyAndLegalState from '@/atoms/showPrivacyAndLegal'
import { friendlyUrl } from '@/utilities/string'
import { isPrimaryColourDark } from '@/utilities/theme'
import { SCREEN_LARGE } from '@/constants/breakpointConstants'
import Box from '../components/Box/Box'
import styles from './PrivacyAndLegal.scss'

const PrivacyAndLegal = () => {
  const t = useLocalization('screens.privacy_and_legal')
  const { width: windowWidth } = useWindowSize()
  const config = useRecoilValue(configState)
  const settings = config.global_settings

  const [showPrivacyAndLegal, setShowPrivacyAndLegal] = useRecoilState(showPrivacyAndLegalState)

  const variants = {
    hide: {
      y: '-100vh',
      transition: { type: 'tween' },
      transitionEnd: {
        display: 'block',
      },
    },
    show: {
      y: 0,
      display: 'block',
      transition: { type: 'tween' },
    },
    exit: {
      y: 0,
      display: 'none',
    },
  }

  const isInlineWidgetOrStandardLayout =
    (config.layout === 'standard' && windowWidth >= SCREEN_LARGE) || config.widget_type === 'inline_embed'

  return (
    <AnimatePresence>
      {showPrivacyAndLegal && (
        <Portal>
          <motion.div
            className={styles.root}
            variants={variants}
            initial='hide'
            animate='show'
            exit={isInlineWidgetOrStandardLayout ? 'exit' : 'hide'}
          >
            <Header />
            <div className={classnames(styles.content, isInlineWidgetOrStandardLayout && styles.inlineWidget)}>
              <h1 className={styles.header}>{t('heading')}</h1>
              <div className={classnames(styles.gradient, styles.headerGradient)} />

              {/* prettier-ignore */}
              <div className={classnames(styles.privacyAndLegalText, isPrimaryColourDark && styles.darkPrimaryColour)}>
                <Box icon={faShieldCheck} text={t('your_personal_information')} />

                <ul className={styles.bulletPoints}>
                  {settings.org_legal_name && (
                    <li>
                      <FontAwesomeIcon icon={faCheckCircle} />
                      <p>
                        {t('we_are')} <strong>{settings.org_legal_name}</strong>
                        {settings.org_legal_address && (<span className={styles.legalAddress}>{settings.org_legal_address}</span>)}
                        {settings.org_legal_number && (<span className={styles.legalNumber}>{t('charity_number', { number: settings.org_legal_number })}</span>)}
                      </p>
                    </li>
                  )}

                  <li>
                    <FontAwesomeIcon icon={faCheckCircle} />
                    <p dangerouslySetInnerHTML={t('bank_level_encryption_html')}></p>
                  </li>

                  <li>
                    <FontAwesomeIcon icon={faCheckCircle} />
                    <p dangerouslySetInnerHTML={t('information_security_standards_html')}></p>
                  </li>

                  <li>
                    <FontAwesomeIcon icon={faCheckCircle} />
                    <p dangerouslySetInnerHTML={t('never_share_your_information_html')}></p>
                  </li>

                  {settings.org_privacy_officer_email && (
                    <li>
                      <FontAwesomeIcon icon={faCheckCircle} />
                      <p>
                        <Translation
                          id='screens.privacy_and_legal.we_are_gdpr_compliant_html'
                          substitutions={{ logging_in: <a key='accounts_login_url' href={config.accounts_login_url} rel='noreferrer' target='_blank'>{t('logging_in')} <FontAwesomeIcon icon={faArrowRight} /></a> }}
                        />
                        <br />
                        <a href={`mailto:${settings.org_privacy_officer_email}`}>{settings.org_privacy_officer_email} <FontAwesomeIcon icon={faArrowRight} /></a>
                      </p>
                    </li>
                  )}
                </ul>

                {settings.org_privacy_policy_url && (
                  <p>
                    {t('access_privacy_policy')}<br />
                    <a href={settings.org_privacy_policy_url} rel='noreferrer' target='_blank'>{friendlyUrl(settings.org_privacy_policy_url)} <FontAwesomeIcon icon={faArrowRight} /></a>
                  </p>
                )}

                {settings.org_privacy_officer_email && (
                  <p>
                    {t('reach_privacy_officer')}<br />
                    <a href={`mailto:${settings.org_privacy_officer_email}`}>{settings.org_privacy_officer_email} <FontAwesomeIcon icon={faArrowRight} /></a>
                  </p>
                )}

                <p>
                  {t('powered_and_secured_by')}<br />
                  <a href='https://givecloud.com' rel='noreferrer' target='_blank'>givecloud.com <FontAwesomeIcon icon={faArrowRight} /></a>
                </p>

                {config.payment_provider_website_url && (
                  <p>
                    {t('payment_safely_processed_by')}<br />
                    <a href={config.payment_provider_website_url} rel='noreferrer' target='_blank'>
                      {friendlyUrl(config.payment_provider_website_url)} <FontAwesomeIcon icon={faArrowRight} />
                    </a>
                  </p>
                )}
              </div>

              <div className={classnames(styles.gradient, styles.footerGradient)} />

              <div className={styles.closeButtonContainer}>
                <button onClick={() => setShowPrivacyAndLegal(false)}>{t('close')}</button>
              </div>
            </div>
          </motion.div>
        </Portal>
      )}
    </AnimatePresence>
  )
}

export default memo(PrivacyAndLegal)
