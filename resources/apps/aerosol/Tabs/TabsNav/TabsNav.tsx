import type { FC, ReactNode, HTMLAttributes } from 'react'
import { Tab } from '@headlessui/react'
import classNames from 'classnames'
import styles from './TabsNav.styles.scss'

type PlacementType = 'start' | 'center' | 'end' | 'between' | 'evenly'

interface Props extends Pick<HTMLAttributes<HTMLDivElement>, 'className'> {
  children: ReactNode
  hasHorizontalScroll?: boolean
  placement?: PlacementType
}

const TabsNav: FC<Props> = ({ children, placement = 'start', hasHorizontalScroll, className, ...rest }) => {
  return (
    <Tab.List
      {...rest}
      className={classNames(styles.root, hasHorizontalScroll && styles.horizontalScroll, styles[placement], className)}
    >
      {children}
    </Tab.List>
  )
}

export { TabsNav }
