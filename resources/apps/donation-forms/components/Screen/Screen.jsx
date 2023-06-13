import { memo, useEffect } from 'react'
import { useSetRecoilState } from 'recoil'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { useHistory } from 'react-router-dom'
import { motion } from 'framer-motion'
import screenHeaderState from '@/atoms/screenHeader'
import useSlideVariants from '@/hooks/useSlideVariants'
import TestModeWarning from './components/TestModeWarning'
import styles from './Screen.scss'

const Screen = ({
  className,
  action,
  controls,
  showHeader = true,
  showBackButton = true,
  showCloseButton = true,
  showLocaleSwitcher = false,
  includeTestMode = true,
  children,
}) => {
  const history = useHistory()
  const hasPopAction = (action || history.action) === 'POP'

  const scrollingVariants = useSlideVariants(hasPopAction)

  const setScreenHeader = useSetRecoilState(screenHeaderState)

  useEffect(() => {
    setScreenHeader({ showHeader, showBackButton, showCloseButton, showLocaleSwitcher })
  }, [setScreenHeader, showHeader, showBackButton, showCloseButton, showLocaleSwitcher])

  const variants = {
    hide: {
      display: 'none',
      transition: { delay: 0.3 },
    },
    show: {
      display: 'flex',
    },
    exit: {
      display: 'none',
      transition: { delay: 0.3 },
    },
  }

  return (
    <motion.div
      className={classnames(styles.root, controls && styles.controls)}
      variants={variants}
      initial='hide'
      animate={controls || 'show'}
      exit='hide'
    >
      <div className={styles.container}>
        <motion.div
          className={classnames(styles.scrolling, controls && styles.controls)}
          variants={scrollingVariants}
          initial='hide'
          animate={controls || 'show'}
          exit='exit'
          transition={{ duration: 0.3 }}
        >
          <div className={classnames(styles.children, showHeader && styles.showHeader, className)}>{children}</div>
        </motion.div>
      </div>

      {includeTestMode && <TestModeWarning className={styles.testModeWarning} />}
    </motion.div>
  )
}

Screen.propTypes = {
  className: PropTypes.string,
  action: PropTypes.oneOf(['POP', 'PUSH']),
  controls: PropTypes.any,
  showBackButton: PropTypes.bool,
  showCloseButton: PropTypes.bool,
  showLocaleSwitcher: PropTypes.bool,
  showHeader: PropTypes.bool,
  includeTestMode: PropTypes.bool,
  children: PropTypes.node,
}

export default memo(Screen)
