import type { FC, HTMLProps } from 'react'
import { AnimatePresence, motion } from 'framer-motion'
import classNames from 'classnames'
import styles from './Thermometer.styles.scss'
import { PRIMARY, CUSTOM_THEME as CUSTOM } from '@/shared/constants/theme'

type Theme = typeof PRIMARY | typeof CUSTOM

interface Props extends Pick<HTMLProps<HTMLDivElement>, 'className' | 'aria-label' | 'aria-hidden'> {
  initialPercentage: number
  additionalPercentage: number
  isThin?: boolean
  theme?: Theme
}

const Thermometer: FC<Props> = ({
  initialPercentage,
  additionalPercentage,
  'aria-label': ariaLabel,
  'aria-hidden': ariaHidden,
  className,
  isThin,
  theme = PRIMARY,
}) => {
  const flexibleBarWidth =
    additionalPercentage + initialPercentage > 100 ? 100 : additionalPercentage + initialPercentage

  const variants = {
    hide: {
      opacity: 0,
      width: initialPercentage,
    },
    show: {
      opacity: 1,
      width: `${flexibleBarWidth}%`,
      transition: { duration: 0.5 },
    },
  }

  return (
    <AnimatePresence>
      <div
        className={classNames(styles.root, styles[theme], isThin && styles.thin, className)}
        role='progressbar'
        aria-label={ariaLabel}
        aria-hidden={ariaHidden}
      >
        <div className={styles.barContainer}>
          <div
            className={classNames(styles.bar, styles.fixed, styles[theme])}
            style={{ width: `${initialPercentage}%` }}
            aria-valuetext={`${initialPercentage}% raised`}
            data-testid='initial-progress'
          />
          <motion.div
            variants={variants}
            initial='hide'
            animate='show'
            className={classNames(styles.bar, styles.flexible, styles[theme])}
            aria-valuetext={`you'll be adding ${additionalPercentage}% to the total`}
            data-testid='additional-progress'
          />
        </div>
      </div>
    </AnimatePresence>
  )
}

export { Thermometer }
