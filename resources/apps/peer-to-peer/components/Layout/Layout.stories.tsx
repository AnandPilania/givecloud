import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { Layout } from './Layout'
import { LayoutContent } from './LayoutContent'
import { LayoutFooter } from './LayoutFooter/LayoutFooter'
import { LayoutHeader } from './LayoutHeader'

export default {
  title: 'Peer to Peer/Layout',
  component: Layout,
} as ComponentMeta<typeof Layout>

const image = 'https://picsum.photos/100/42'

export const Default: ComponentStory<typeof Layout> = () => {
  return (
    <Layout image={image}>
      <LayoutHeader>
        <a href='#'>
          <img src={image} alt='' />
        </a>
      </LayoutHeader>
      <LayoutContent>content</LayoutContent>
      <LayoutFooter>footer</LayoutFooter>
    </Layout>
  )
}
