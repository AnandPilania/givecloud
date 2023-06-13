import type { FC, PropsWithChildren } from 'react'
import type { HTMLMotionProps } from 'framer-motion'
import { AnimatePresence, motion } from 'framer-motion'

const animationMap = {
  left: {
    initial: { opacity: 0, x: '-30%' },
    animate: { opacity: 1, x: 0 },
    exit: { opacity: 0, x: '-30%' },
  },
  right: {
    initial: { opacity: 0, x: '30%' },
    animate: { opacity: 1, x: 0 },
    exit: { opacity: 0, x: '30%' },
  },
  bottom: {
    initial: { opacity: 0, y: '30%' },
    animate: { opacity: 1, y: '0%' },
    exit: { opacity: 0, y: '30%' },
  },
  top: {
    initial: { opacity: 0, y: '-30%' },
    animate: { opacity: 1, y: '0%' },
    exit: { opacity: 0, y: '-30%' },
  },
} as const

type AnimateFrom = keyof typeof animationMap

interface Props extends PropsWithChildren, HTMLMotionProps<'div'> {
  slideInFrom: AnimateFrom
}

const SlideAnimation: FC<Props> = ({ children, slideInFrom, className, ...rest }) => {
  const { initial, animate, exit } = animationMap[slideInFrom]

  return (
    <AnimatePresence>
      <motion.div
        initial={initial}
        animate={animate}
        exit={exit}
        transition={{ duration: 1 }}
        className={className}
        {...rest}
      >
        {children}
      </motion.div>
    </AnimatePresence>
  )
}

export { SlideAnimation }
