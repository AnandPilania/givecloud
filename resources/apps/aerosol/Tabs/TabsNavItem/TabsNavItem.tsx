import type { FC, ReactNode, HTMLAttributes } from 'react'
import classNames from 'classnames'
import { Tab } from '@headlessui/react'
import { useTabsContext } from '@/aerosol/Tabs/TabsContext'
import styles from './TabsNavItem.styles.scss'

interface Props extends Pick<HTMLAttributes<HTMLButtonElement>, 'className' | 'onClick'> {
  children: ReactNode
  isDisabled?: boolean
  name?: string
  value?: string
}

const TabsNavItem: FC<Props> = ({ children, isDisabled, className, ...rest }) => {
  const { invertTheme } = useTabsContext()

  return (
    <Tab
      {...rest}
      disabled={isDisabled}
      className={({ selected }) =>
        classNames(
          styles.root,
          invertTheme && styles.inverted,
          selected && styles.selected,
          isDisabled && styles.disabled,
          className
        )
      }
    >
      {children}
    </Tab>
  )
}

export { TabsNavItem }
export type { Props as TabNavItemProps }
