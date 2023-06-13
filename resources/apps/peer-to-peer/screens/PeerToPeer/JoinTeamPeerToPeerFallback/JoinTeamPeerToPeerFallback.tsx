import type { FC } from 'react'
import { LayoutHeader, LayoutContent, LayoutFooter, Layout, HeroAvatar, Text } from '@/components'
import { SlideAnimation } from '@/shared/components/SlideAnimation'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { FallbackWidget } from './FallbackWidget'
import styles from './JoinTeamPeerToPeerFallback.styles.scss'

const JoinTeamPeerToPeerFallback: FC = () => {
  const {
    fundraisingExperience: { background_url, landing_page_description, landing_page_headline, logo_url },
  } = useFundraisingExperienceState()

  return (
    <Layout image={background_url} widget={<FallbackWidget />}>
      <LayoutHeader>
        <img src={logo_url} className='w-20' />
      </LayoutHeader>
      <LayoutContent>
        <SlideAnimation slideInFrom='top' className={styles.text}>
          <HeroAvatar src={background_url} />
        </SlideAnimation>
        <SlideAnimation slideInFrom='bottom' className={styles.text}>
          <Text type='h1'>{landing_page_headline}</Text>
          <Text type='h2'>{landing_page_description}</Text>
        </SlideAnimation>
      </LayoutContent>
      <LayoutFooter>
        <PeerToPeerFooter />
      </LayoutFooter>
    </Layout>
  )
}

export { JoinTeamPeerToPeerFallback }
