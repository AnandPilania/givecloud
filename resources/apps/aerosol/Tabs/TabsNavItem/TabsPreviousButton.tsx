import type { FC, HTMLProps } from 'react'
import type { TabNavItemProps } from './TabsNavItem'
import classNames from 'classnames'
import { useTabsContext } from '@/aerosol/Tabs/TabsContext'
import styles from './TabsNavItem.styles.scss'

type Props = HTMLProps<HTMLButtonElement> & TabNavItemProps

const TabsPreviousButton: FC<Props> = ({ children, isDisabled, className, ...rest }) => {
  const { setSelectedIndex, numberOfTabs, selectedIndex = 0, invertTheme } = useTabsContext()

  const handlePreviousButton = () => {
    if (selectedIndex === 0) {
      setSelectedIndex(numberOfTabs - 1)
    } else {
      setSelectedIndex(selectedIndex - 1)
    }
  }

  return (
    <button
      {...rest}
      type='button'
      className={classNames(styles.root, invertTheme && styles.inverted, isDisabled && styles.disabled, className)}
      onClick={handlePreviousButton}
    >
      {children}
    </button>
  )
}

export { TabsPreviousButton }
