import type { FC, HTMLProps } from 'react'
import type { TabNavItemProps } from './TabsNavItem'
import classNames from 'classnames'
import { useTabsContext } from '@/aerosol/Tabs/TabsContext'
import styles from './TabsNavItem.styles.scss'

type Props = HTMLProps<HTMLButtonElement> & TabNavItemProps

const TabsNextButton: FC<Props> = ({ children, isDisabled, className, ...rest }) => {
  const { setSelectedIndex, numberOfTabs, selectedIndex = 0, invertTheme } = useTabsContext()

  const handleNextButton = () => {
    if (selectedIndex === numberOfTabs - 1) {
      setSelectedIndex(0)
    } else {
      setSelectedIndex(selectedIndex + 1)
    }
  }

  return (
    <button
      {...rest}
      type='button'
      disabled={isDisabled}
      className={classNames(styles.root, invertTheme && styles.inverted, isDisabled && styles.disabled, className)}
      onClick={handleNextButton}
    >
      {children}
    </button>
  )
}

export { TabsNextButton }
