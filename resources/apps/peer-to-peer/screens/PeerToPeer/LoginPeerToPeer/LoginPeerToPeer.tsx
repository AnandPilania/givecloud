import type { FC } from 'react'
import { Redirect } from 'react-router'
import { CREATE_PATH } from '@/constants/paths'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { LayoutHeader, LayoutFooter, Layout, LayoutContent, Text, HeroAvatar } from '@/components'
import { LoginWidget } from './LoginWidget'
import { SlideAnimation } from '@/shared/components/SlideAnimation'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { useSupporterState } from '@/screens/PeerToPeer/useSupporterState'
import styles from './LoginPeerToPeer.styles.scss'

const LoginPeerToPeer: FC = () => {
  const {
    fundraisingExperience: { background_url, landing_page_headline, landing_page_description, logo_url },
  } = useFundraisingExperienceState()

  const { isAuthenticated } = useSupporterState()

  if (isAuthenticated) {
    return <Redirect to={{ pathname: CREATE_PATH }} />
  }

  return (
    <Layout widget={<LoginWidget />} image={background_url}>
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

export { LoginPeerToPeer }
