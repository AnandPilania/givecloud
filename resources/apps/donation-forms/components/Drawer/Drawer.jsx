import PropTypes from 'prop-types'
import classnames from 'classnames'
import { AnimatePresence, motion } from 'framer-motion'
import { FocusTrap } from '@headlessui/react'
import Portal from '@/components/Portal/Portal'
import { noop } from '@/utilities/helpers'
import styles from './Drawer.scss'

const Drawer = ({ className = '', children, open, onClose = noop, initialFocus }) => {
  const focusTrapFeatures = open ? 30 : 1

  const backdropVariants = {
    hide: {
      opacity: 0,
      transitionEnd: {
        display: 'none',
      },
    },
    show: {
      opacity: 0.75,
      display: 'block',
    },
  }

  return (
    <Portal className={classnames(styles.root, open && styles.open)}>
      <AnimatePresence>
        {open && (
          <motion.div
            variants={backdropVariants}
            initial='hide'
            animate='show'
            exit='hide'
            className={styles.overlay}
            onClick={onClose}
          ></motion.div>
        )}
      </AnimatePresence>
      <div className={classnames(styles.content, open && styles.isVisible)}>
        <FocusTrap className={styles.inner} initialFocus={initialFocus} features={focusTrapFeatures}>
          <button type='button' className={styles.closeBtn} onClick={onClose}></button>
          <div className={className}>{children}</div>
        </FocusTrap>
      </div>
    </Portal>
  )
}

Drawer.propTypes = {
  className: PropTypes.string,
  children: PropTypes.node,
  open: PropTypes.bool.isRequired,
  onClose: PropTypes.func,
  initialFocus: PropTypes.any,
}

export default Drawer
