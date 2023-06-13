import type { FC } from 'react'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { LayoutHeader, LayoutContent, LayoutFooter, Layout, HeroAvatar, Text } from '@/components'
import { CreatePeerToPeerWidget } from './CreatePeerToPeerWidget'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import styles from './PeerToPeer.styles.scss'

const CreatePeerToPeer: FC = () => {
  const {
    fundraisingExperience: { background_url, landing_page_headline, landing_page_description, logo_url },
  } = useFundraisingExperienceState()

  return (
    <Layout widget={<CreatePeerToPeerWidget />} image={background_url} initWidgetAnimation={false}>
      <LayoutHeader>
        <img src={logo_url} className='w-20' />
      </LayoutHeader>
      <LayoutContent>
        <div className={styles.text}>
          <HeroAvatar preventAnimation src={background_url} />
          <Text type='h1'>{landing_page_headline}</Text>
          <Text type='h2'>{landing_page_description}</Text>
        </div>
      </LayoutContent>
      <LayoutFooter>
        <PeerToPeerFooter />
      </LayoutFooter>
    </Layout>
  )
}

export { CreatePeerToPeer }
