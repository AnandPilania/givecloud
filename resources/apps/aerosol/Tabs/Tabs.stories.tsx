import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { Tabs } from './Tabs'
import { TabsNav } from './TabsNav'
import { TabsPanels } from './TabsPanels'
import { TabsPanel } from './TabsPanel'
import { TabsNextButton, TabsPreviousButton, TabsNavItem } from './TabsNavItem'

export default {
  title: 'Aerosol/Tabs',
  component: Tabs,
  args: {
    invertTheme: false,
  },
  argTypes: {
    invertTheme: {
      control: 'boolean',
    },
  },
} as ComponentMeta<typeof Tabs>

export const Default: ComponentStory<typeof Tabs> = (args) => (
  <Tabs {...args}>
    <TabsNav>
      <TabsPreviousButton>Previous</TabsPreviousButton>
      <TabsNavItem>one</TabsNavItem>
      <TabsNavItem>two</TabsNavItem>
      <TabsNavItem>three</TabsNavItem>
      <TabsNextButton>Next</TabsNextButton>
    </TabsNav>
    <TabsPanels>
      <TabsPanel key={1}>
        <div className='bg-red-400 text-white w-52 h-52'>one</div>
      </TabsPanel>
      <TabsPanel key={2}>
        <div className='bg-green-400 text-white w-52 h-52'>two</div>
      </TabsPanel>
      <TabsPanel key={3}>
        <div className='bg-blue-400 text-white w-52 h-52'>three</div>
      </TabsPanel>
    </TabsPanels>
  </Tabs>
)
