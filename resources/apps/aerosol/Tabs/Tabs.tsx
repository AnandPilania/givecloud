import type { FC, HTMLProps } from 'react'
import { useState, Children, isValidElement, useMemo, useEffect } from 'react'
import { Tab } from '@headlessui/react'
import { TabsContext } from '@/aerosol/Tabs/TabsContext'
import { TabsPanels } from '@/aerosol/Tabs/TabsPanels'

interface Props extends Omit<HTMLProps<HTMLDivElement>, 'as' | 'onChange'> {
  initialIndex?: number
  invertTheme?: boolean
}
const TabsPanelsType = (<TabsPanels />).type

const Tabs: FC<Props> = ({ initialIndex = 0, children, invertTheme = false, ...rest }) => {
  const [selectedIndex, setSelectedIndex] = useState(initialIndex)

  useEffect(() => {
    if (selectedIndex === initialIndex) return
    setSelectedIndex(initialIndex)
  }, [initialIndex])

  const getNumberOfTabs = () => {
    let tabs = 0
    Children.forEach(children, (child) => {
      if (isValidElement(child) && child.type === TabsPanelsType) {
        tabs = Children.count(child.props.children)
      }
    })
    return tabs
  }

  const numberOfTabs = useMemo(getNumberOfTabs, [children])

  return (
    <TabsContext.Provider value={{ selectedIndex, setSelectedIndex, numberOfTabs, invertTheme }}>
      <Tab.Group {...rest} onChange={setSelectedIndex} selectedIndex={selectedIndex}>
        {children}
      </Tab.Group>
    </TabsContext.Provider>
  )
}

Tabs.defaultProps = {
  initialIndex: 0,
  invertTheme: false,
}

export { Tabs }
