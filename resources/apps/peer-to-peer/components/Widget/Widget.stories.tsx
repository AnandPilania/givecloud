import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { faRocketLaunch } from '@fortawesome/pro-regular-svg-icons'
import { Button, Carousel, Text } from '@/aerosol'
import { HeroAvatar } from '../HeroAvatar'
import { Layout, LayoutHeader, LayoutContent, LayoutFooter } from '../Layout'
import { Widget } from './Widget'
import { WidgetContent } from './WidgetContent'
import { WidgetFooter } from './WidgetFooter'
import { WidgetHeader } from './WidgetHeader'

export default {
  title: 'Peer to Peer/Widget',
  component: Widget,
} as ComponentMeta<typeof Widget>

export const Default: ComponentStory<typeof Widget> = () => {
  const widget = (
    <Widget>
      <Carousel name='for the love of god'>
        <WidgetHeader onCloseHref='/' indexToNavigate='0'>
          <a href='#'>
            <img src='https://picsum.photos/100/42' alt='close button of some kind' />
          </a>
        </WidgetHeader>
        <WidgetContent>
          <HeroAvatar icon={faRocketLaunch} />
          <Text isBold type='h3'>
            Start a social challenge
          </Text>
          <Text isMarginless isBold>
            Compete with your friends to raise money
          </Text>
        </WidgetContent>
        <WidgetFooter>
          <Button>hello</Button>
        </WidgetFooter>
      </Carousel>
    </Widget>
  )

  return (
    <Layout widget={widget}>
      <LayoutHeader>header</LayoutHeader>
      <LayoutContent>content</LayoutContent>
      <LayoutFooter>footer</LayoutFooter>
    </Layout>
  )
}
