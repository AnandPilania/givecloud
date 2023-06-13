import type { FC, ReactNode, ComponentType } from 'react'
import { Tab } from '@headlessui/react'
import { motion } from 'framer-motion'

const scaleTabsAnimation = {
  initial: {
    y: 10,
    opacity: 0,
    scale: 0.5,
  },
  animate: {
    y: 0,
    opacity: 1,
    scale: 1,
  },
  exit: {
    y: -10,
    opacity: 0,
  },
  transition: {
    duration: 0.5,
  },
}

const defaultTabsAnimation = {
  initial: {
    y: 15,
    opacity: 0,
  },
  animate: {
    y: 0,
    opacity: 1,
  },
  exit: {
    y: -15,
    opacity: 0,
  },
  transition: {
    duration: 0.4,
    ease: 'easeOut',
  },
}

const animations = {
  default: defaultTabsAnimation,
  scale: scaleTabsAnimation,
}

type ExtractProps<T> = T extends ComponentType<infer P> ? P : T
type HeadlessTabPanelProps = ExtractProps<typeof Tab.Panel>

interface Props {
  children: ReactNode
  animationType?: string
}

const TabsPanel: FC<Props & HeadlessTabPanelProps> = ({ children, animationType = 'default', ...rest }) => {
  const tabsAnimation = animations[animationType]

  return (
    <Tab.Panel {...rest} as={motion.div} {...tabsAnimation} aria-live='polite'>
      {children}
    </Tab.Panel>
  )
}

export { TabsPanel }
