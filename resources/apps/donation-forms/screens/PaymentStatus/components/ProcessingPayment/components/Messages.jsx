import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import PropTypes from 'prop-types'
import useLocalization from '@/hooks/useLocalization'

const Messages = ({ className }) => {
  const t = useLocalization('screens.processing_payment')

  const messages = useMemo(
    () => [
      t('messages.contacting_bank'),
      t('messages.authorizing_payment'),
      t('messages.waiting_on_bank'),
      t('messages.processing'),
    ],
    [t]
  )

  const timeout = useRef(null)
  const [messageIndex, setMessageIndex] = useState(-1)

  const message = messages?.[messageIndex] || null

  const queueUpNextMessage = useCallback(() => {
    if (messageIndex < messages.length - 1) {
      clearTimeout(timeout.current)
      timeout.current = setTimeout(() => setMessageIndex(messageIndex + 1), (messageIndex + 1) * 1200)
    }
  }, [messages, messageIndex, timeout, setMessageIndex])

  useEffect(() => {
    if (messageIndex === -1) {
      queueUpNextMessage()
    }

    return () => clearTimeout(timeout.current)
  }, [messageIndex, timeout, queueUpNextMessage])

  return (
    <AnimatePresence exitBeforeEnter>
      {message && (
        <motion.div
          key={messageIndex}
          initial={{ opacity: 0, x: '-100%' }}
          animate={{ opacity: 1, x: 0 }}
          exit={{ opacity: 0, x: '100%' }}
          transition={{ duration: 0.3 }}
          onAnimationComplete={queueUpNextMessage}
        >
          <p className={className}>{message}</p>
        </motion.div>
      )}
    </AnimatePresence>
  )
}

Messages.propTypes = {
  className: PropTypes.string,
}

export default Messages
