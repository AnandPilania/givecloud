import { memo, useRef, useState } from 'react'
import { useEffectOnce, useWindowSize } from 'react-use'
import { useLocation } from 'react-router-dom'
import { useRecoilValue } from 'recoil'
import { random } from 'lodash'
import { AnimatePresence, motion } from 'framer-motion'
import { delay } from 'nanodelay'
import easeOutElastic from 'eases/elastic-out'
import { CHOOSE_PAYMENT_METHOD, LANDING, PAY_WITH_CREDIT_CARD } from '@/constants/pathConstants'
import SocialProof from './components/SocialProof/SocialProof'
import configState from '@/atoms/config'
import paymentStatusState from '@/atoms/paymentStatus'
import styles from './SocialProofNotifications.scss'
import Portal from '@/components/Portal/Portal'
import { SCREEN_LARGE } from '@/constants/breakpointConstants'

const routesIncludingNotifications = [LANDING, CHOOSE_PAYMENT_METHOD, PAY_WITH_CREDIT_CARD]

const SocialProofNotifications = () => {
  const config = useRecoilValue(configState)
  const paymentStatus = useRecoilValue(paymentStatusState)
  const location = useLocation()
  const { width: windowWidth } = useWindowSize()

  const isLargeScreenAndStandardLayout = windowWidth >= SCREEN_LARGE && config.layout === 'standard'

  // prettier-ignore
  const showNotifications = config.social_proof.enabled
    && config.social_proof.proofs.length > 0
    && (isLargeScreenAndStandardLayout || routesIncludingNotifications.includes(location.pathname))
    && paymentStatus === null

  const showNotificationsRef = useRef(showNotifications)
  showNotificationsRef.current = showNotifications

  const [socialProofIndex, setSocialProofIndex] = useState(null)
  const socialProof = showNotifications && config.social_proof.proofs?.[socialProofIndex]
  useEffectOnce(() => {
    const showNotification = async (index) => {
      if (showNotificationsRef.current) {
        await delay(random(2000, 8000))
        setSocialProofIndex(index)
        await delay(3500)
        setSocialProofIndex(null)

        const nextIndex = index === config.social_proof.proofs.length - 1 ? 0 : index + 1
        showNotification(nextIndex)
      }
    }

    showNotification(0)
  })

  const variants = {
    hide: {
      y: -100,
      transition: { ease: 'easeIn' },
    },
    show: {
      y: 34,
      transition: { ease: easeOutElastic, duration: 1 },
    },
  }

  return (
    <Portal fullscreen={true}>
      <AnimatePresence>
        {socialProof && (
          <motion.div className={styles.root} variants={variants} animate='show' exit='hide' style={{ x: '-50%' }}>
            <SocialProof socialProof={socialProof} onClick={() => setSocialProofIndex(null)} />
          </motion.div>
        )}
      </AnimatePresence>
    </Portal>
  )
}

export default memo(SocialProofNotifications)
