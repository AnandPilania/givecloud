import { useSetRecoilState } from 'recoil'
import showFAQState from '@/atoms/showFAQ'
import showPrivacyAndLegalState from '@/atoms/showPrivacyAndLegal'
import useAnalytics from '@/hooks/useAnalytics'
import useLocalization from '@/hooks/useLocalization'

const useFooterLinks = () => {
  const setShowFAQ = useSetRecoilState(showFAQState)
  const setShowPrivacyAndLegal = useSetRecoilState(showPrivacyAndLegalState)
  const collectEvent = useAnalytics({ collectOnce: true })
  const t = useLocalization()

  const onClickFAQ = () => {
    setShowPrivacyAndLegal(false)
    setShowFAQ(true)
    collectEvent({ event_name: 'faq_click' })
  }

  const onClickPrivacyAndLegal = () => {
    setShowFAQ(false)
    setShowPrivacyAndLegal(true)
    collectEvent({ event_name: 'privacy_and_legal_click' })
  }

  const footerLinks = [
    { label: t('components.footer_navigation.faq'), onClick: onClickFAQ },
    { label: t('components.footer_navigation.privacy_and_legal'), onClick: onClickPrivacyAndLegal },
  ]

  return { footerLinks }
}

export default useFooterLinks
