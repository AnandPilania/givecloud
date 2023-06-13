import type { Dispatch, SetStateAction } from 'react'
import { createContext, useContext } from 'react'

interface TabContextData {
  setSelectedIndex: Dispatch<SetStateAction<number>>
  selectedIndex: number
  numberOfTabs: number
  invertTheme?: boolean
}

const TabsContext = createContext<TabContextData | null>(null)

const useTabsContext = () => {
  const context = useContext(TabsContext)
  if (context === null) throw new Error('useTabsContext is not being used within a provider')
  return context
}

export { TabsContext, useTabsContext }
