import { memo } from 'react'
import { useRecoilState, useRecoilValue } from 'recoil'
import { useWindowSize } from 'react-use'
import classnames from 'classnames'
import { AnimatePresence, motion } from 'framer-motion'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faLockAlt, faCheck, faShieldCheck, faArrowRight } from '@fortawesome/pro-light-svg-icons'
import { isPrimaryColourDark } from '@/utilities/theme'
import { SCREEN_LARGE } from '@/constants/breakpointConstants'
import useLocalization from '@/hooks/useLocalization'
import Portal from '@/components/Portal/Portal'
import Header from '@/components/Screen/components/Header/Header'
import configState from '@/atoms/config'
import showFAQState from '@/atoms/showFAQ'
import Box from '../components/Box/Box'
import styles from './FrequentlyAskedQuestions.scss'

const FrequentlyAskedQuestions = () => {
  const {
    global_settings: settings,
    transparency_promise,
    widget_type: widgetType,
    layout,
  } = useRecoilValue(configState)
  const [showFAQ, setShowFAQ] = useRecoilState(showFAQState)

  const { width: windowWidth } = useWindowSize()

  const t = useLocalization('screens.frequently_asked_questions')

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

  const isStandardLayout = layout === 'standard' && windowWidth >= SCREEN_LARGE
  const isInlineWidgetOrStandardLayout = isStandardLayout || widgetType === 'inline_embed'

  return (
    <AnimatePresence>
      {showFAQ && (
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
              <div className={classnames(styles.faqText,isPrimaryColourDark && styles.darkPrimaryColour)}>
                <Box icon={faLockAlt} dangerouslySetInnerHTML={t('bank_level_security_html')} />

                {Boolean(settings.org_legal_country && settings.org_legal_number) && (
                  <Box
                    icon={faCheck}
                    dangerouslySetInnerHTML={t('tax_deductible_html', {
                      country: settings.org_legal_country,
                      type: settings.org_legal_country === 'US' ? '501(c)(3)' : '',
                      number: settings.org_legal_number,
                    })}
                  />
                )}

                {/*
                  <p className={styles.box}>
                    <FontAwesomeIcon icon={faEnvelope} />
                    <span dangerouslySetInnerHTML={t('instant_tax_receipt_html')}></span>
                  </p>
                */}

                {transparency_promise.enabled && (
                  <Box icon={faShieldCheck} dangerouslySetInnerHTML={t('transparency_promise_html')} />
                )}

                {Boolean(settings.org_legal_name && settings.org_check_mailing_address) && (
                  <p>
                    <span dangerouslySetInnerHTML={t('give_by_check_html')}></span><br />
                    <span className={styles.mailingAddress}>
                      {settings.org_legal_name}<br />
                      {settings.org_check_mailing_address}
                    </span>
                  </p>
                )}

                {settings.org_other_ways_to_donate?.length > 0 && (
                  <div>
                    <strong>{t('other_ways_to_donate')}</strong><br />
                    <ul>
                      {settings.org_other_ways_to_donate?.map((item, index) => (
                        <li key={index}><a href={item.href} rel='noreferrer' target='_blank'>{item.label} <FontAwesomeIcon icon={faArrowRight} /></a></li>
                      ))}
                    </ul>
                  </div>
                )}

                {(settings.org_support_number || settings.org_support_email) && (
                  <p>
                    <span dangerouslySetInnerHTML={t('contact_us_html')}></span><br />
                    {settings.org_support_number && (
                      <><a href={`tel:${settings.org_support_number}`}>{settings.org_support_number} <FontAwesomeIcon icon={faArrowRight} /></a><br /></>
                    )}
                    {settings.org_support_email && (
                      <><a href={`mailto:${settings.org_support_email}`}>{settings.org_support_email} <FontAwesomeIcon icon={faArrowRight} /></a></>
                    )}
                  </p>
                )}

                {Boolean(settings.org_faq_alternative_question && settings.org_faq_alternative_answer) && (
                  <p>
                    <strong>{settings.org_faq_alternative_question}</strong><br />{settings.org_faq_alternative_answer}
                  </p>
                )}
              </div>

              <div className={classnames(styles.gradient, styles.footerGradient)} />

              <div className={styles.closeButtonContainer}>
                <button onClick={() => setShowFAQ(false)}>{t('close')}</button>
              </div>
            </div>
          </motion.div>
        </Portal>
      )}
    </AnimatePresence>
  )
}

export default memo(FrequentlyAskedQuestions)
