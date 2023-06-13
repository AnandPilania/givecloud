import { memo, useState } from 'react'
import PropTypes from 'prop-types'
import { AnimatePresence, motion } from 'framer-motion'
import classnames from 'classnames'
import Emoji from 'react-emoji-render'
import styles from './AnimatedIcons.scss'

const AnimatedIcons = ({ src = null, text = '❤️', large = false, offset = null }) => {
  const [hasAnimated, setHasAnimated] = useState(false)

  if (hasAnimated) {
    return null
  }

  const onComplete = (icon) => {
    if (icon === 3 || (icon === 2 && !large)) {
      setHasAnimated(true)
    }
  }

  const x = large ? 10 : 5
  const scale = large ? 3 : 1

  const floatingIcon = src ? <img src={src} /> : <Emoji text={text} />

  const icon1 = (
    <motion.span
      initial={{ opacity: 0 }}
      animate={{
        opacity: [0, 0.8, 0],
        y: [0, large ? -100 : -70],
        x: [-x, x, -x, x],
        scale,
        transition: { duration: 1 },
      }}
      className={styles.icon1}
      onAnimationComplete={() => onComplete(1)}
    >
      {floatingIcon}
    </motion.span>
  )

  const icon2 = (
    <motion.span
      animate={{
        opacity: [0, 0.7, 0],
        y: [0, large ? -80 : -60],
        x: [x, -x, x, -x],
        scale,
        transition: { duration: 1, delay: 0.2 },
      }}
      className={styles.icon2}
      onAnimationComplete={() => onComplete(2)}
    >
      {floatingIcon}
    </motion.span>
  )

  const icon3 = (
    <motion.span
      animate={{
        opacity: [0, 0.7, 0],
        y: [0, large ? -60 : -50],
        x: [-x, x, -x, x],
        scale,
        transition: { duration: 1, delay: 0.4 },
      }}
      className={styles.icon3}
      onAnimationComplete={() => onComplete(3)}
    >
      {floatingIcon}
    </motion.span>
  )

  return (
    <AnimatePresence>
      <motion.span
        className={classnames(styles.root, !offset && styles.center, large && styles.large)}
        style={!!offset && { top: offset.top, left: offset.left }}
        exit={{ opacity: 0 }}
      >
        {icon1}
        {icon2}
        {large && icon3}
      </motion.span>
    </AnimatePresence>
  )
}

AnimatedIcons.propTypes = {
  src: PropTypes.string,
  text: PropTypes.string,
  large: PropTypes.bool,
  offset: PropTypes.object,
}

export default memo(AnimatedIcons)
