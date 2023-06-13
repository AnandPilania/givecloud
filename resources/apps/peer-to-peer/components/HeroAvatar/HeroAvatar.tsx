import type { FC, HTMLProps, PropsWithChildren } from 'react'
import type { IconProp } from '@fortawesome/fontawesome-svg-core'
import { PRIMARY, CUSTOM_THEME as CUSTOM } from '@/shared/constants/theme'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { AnimatePresence, motion } from 'framer-motion'
import classnames from 'classnames'
import styles from './HeroAvatar.styles.scss'

const backgroundVariants = {
  hide: {
    opacity: 0,
    scale: 0,
  },
  show: {
    opacity: 1,
    scale: 1,
    transition: { duration: 0.4, delay: 0.2 },
  },
}

const iconVariants = {
  hide: {
    opacity: 0,
    y: '-80%',
  },
  show: {
    opacity: 1,
    y: '0%',
    transition: { duration: 0.5, delay: 0.5 },
  },
}

type Theme = typeof PRIMARY | typeof CUSTOM

interface Props extends Omit<HTMLProps<HTMLImageElement>, 'crossOrigin' | 'size'>, PropsWithChildren {
  icon?: IconProp
  initAnimationOn?: boolean
  preventAnimation?: boolean
  isMarginless?: boolean
  objectFit?: 'cover' | 'contain'
  size?: 'small' | 'large'
  initials?: string
  theme?: Theme
}

const HeroAvatar: FC<Props> = ({
  className,
  objectFit = 'cover',
  icon,
  initAnimationOn = true,
  preventAnimation,
  isMarginless,
  src,
  children,
  size = 'large',
  initials,
  theme = CUSTOM,
  ...rest
}) => {
  const css = classnames(styles.root, styles[size], !isMarginless && 'mb-4', styles[theme])

  const renderContent = () => {
    const css = classnames(styles.root, styles[size], styles[objectFit])

    if (icon) return <FontAwesomeIcon icon={icon} size='2x' aria-hidden='true' />
    if (src) return <img alt='' src={src} {...rest} className={css} />
    if (initials) return <div className={classnames(styles.initials, styles[size])}>{initials}</div>

    return <div className={css}>{children}</div>
  }

  const renderIcon = () => {
    const initial = preventAnimation ? false : 'hide'
    if (initAnimationOn) {
      return (
        <motion.div variants={backgroundVariants} initial={initial} animate='show' className={css}>
          <motion.div variants={iconVariants} initial={initial} animate='show'>
            {renderContent()}
          </motion.div>
        </motion.div>
      )
    }
    return <div className={classnames(css, 'invisible')} />
  }

  return (
    <div className={classnames('inline', className)}>
      <AnimatePresence>{renderIcon()}</AnimatePresence>
    </div>
  )
}

export { HeroAvatar }
