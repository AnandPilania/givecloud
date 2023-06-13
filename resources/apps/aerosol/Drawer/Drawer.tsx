import type { FC, HTMLProps } from 'react'
import type { PortalProps } from '@/aerosol/Portal'
import classnames from 'classnames'
import { AnimatePresence, motion } from 'framer-motion'
import { Portal } from '@/aerosol/Portal'
import styles from './Drawer.styles.scss'

interface Props extends Pick<HTMLProps<HTMLDivElement>, 'className'>, PortalProps {
  isOpen: boolean
  onClose: () => void
  isFullHeight?: boolean
  showCloseButton?: boolean
}

const backdropVariants = {
  hide: {
    opacity: 0,
    transitionEnd: { display: 'none' },
  },
  show: {
    opacity: 0.3,
    display: 'block',
  },
}

const contentVariants = {
  hide: {
    opacity: 0,
    y: '100%',
    transition: { duration: 0.3 },
  },
  show: {
    opacity: 1,
    y: '0',
    transition: { duration: 0.3 },
  },
}

const Drawer: FC<Props> = ({ className, children, name, isOpen, onClose, isFullHeight, showCloseButton = true }) => {
  const renderButton = () =>
    showCloseButton ? (
      <button type='button' onClick={onClose} aria-label={`close ${name} drawer`} className={styles.closeBtn}>
        <div className={styles.buttonBar}></div>
      </button>
    ) : null

  const renderOverlay = () =>
    isOpen ? (
      <motion.div
        variants={backdropVariants}
        initial='hide'
        animate='show'
        exit='hide'
        className={styles.overlay}
        onClick={onClose}
      />
    ) : null

  const renderContent = () =>
    isOpen ? (
      <motion.div
        variants={contentVariants}
        initial='hide'
        animate='show'
        exit='hide'
        className={classnames(styles.content, isFullHeight ? styles.fullHeight : styles.default)}
      >
        <div className={styles.inner}>
          {renderButton()}
          {children}
        </div>
      </motion.div>
    ) : null

  return (
    <>
      <div className={classnames(styles.root, isOpen && styles.open)} id={name} />
      <Portal name={name}>
        <AnimatePresence>{renderOverlay()}</AnimatePresence>
        <AnimatePresence>{renderContent()}</AnimatePresence>
      </Portal>
    </>
  )
}

export { Drawer }
export type { Props as DrawerProps }
