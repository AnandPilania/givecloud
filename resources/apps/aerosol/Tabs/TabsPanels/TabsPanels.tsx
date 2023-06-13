import type { FC, HTMLAttributes, ReactNode } from 'react'
import { isValidElement, cloneElement, Children } from 'react'
import classNames from 'classnames'
import { AnimatePresence } from 'framer-motion'
import { Tab } from '@headlessui/react'
import styles from './TabsPanels.styles.scss'

interface Props extends Pick<HTMLAttributes<HTMLDivElement>, 'className'> {
  children?: ReactNode
  animationType?: string
}

interface TabPanelsChild {
  animationType?: string
}

const TabsPanels: FC<Props> = ({ children, animationType = 'default', className, ...rest }) => {
  return (
    <Tab.Panels {...rest} className={classNames(styles.root, className)}>
      <AnimatePresence>
        {Children.map(children, (child) =>
          isValidElement<TabPanelsChild>(child) ? cloneElement(child, { animationType }) : null
        )}
      </AnimatePresence>
    </Tab.Panels>
  )
}

export { TabsPanels }
