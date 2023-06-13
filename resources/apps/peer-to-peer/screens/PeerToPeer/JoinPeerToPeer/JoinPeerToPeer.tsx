import type { FC } from 'react'
import { useEffect } from 'react'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { LayoutHeader, LayoutContent, LayoutFooter, Layout, HeroAvatar, Text } from '@/components'
import { JoinPeerToPeerWidget } from './JoinPeerToPeerWidget'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import styles from './JoinPeerToPeer.styles.scss'

const JoinPeerToPeer: FC = () => {
  const {
    fundraisingExperience: { background_url, landing_page_description, logo_url },
  } = useFundraisingExperienceState()
  const { team, setPeerToPeerState, peerToPeerValue } = usePeerToPeerState()

  useEffect(() => setPeerToPeerState({ ...peerToPeerValue, avatarName: 'custom' }), [])

  return (
    <Layout widget={<JoinPeerToPeerWidget />} image={background_url}>
      <LayoutHeader>
        <img src={logo_url} className='w-20' />
      </LayoutHeader>
      <LayoutContent>
        <div className={styles.text}>
          <HeroAvatar preventAnimation src={background_url} />
          <Text type='h1'>{team.name}</Text>
          <Text type='h2'>{landing_page_description}</Text>
        </div>
      </LayoutContent>
      <LayoutFooter>
        <PeerToPeerFooter />
      </LayoutFooter>
    </Layout>
  )
}

export { JoinPeerToPeer }
