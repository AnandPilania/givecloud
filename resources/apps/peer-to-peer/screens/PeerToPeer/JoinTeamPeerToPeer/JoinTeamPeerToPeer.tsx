import type { FC } from 'react'
import { JOIN_TEAM_PATH } from '@/constants/paths'
import { triggerToast } from '@/aerosol'
import { LayoutHeader, LayoutContent, LayoutFooter, Layout, HeroAvatar, Text } from '@/components'
import { SlideAnimation } from '@/shared/components/SlideAnimation'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { JoinTeamPeerToPeerWidget } from './JoinTeamPeerToPeerWidget'
import { useParams } from '@/shared/hooks'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import { useTeamPeerToPeerQuery } from '@/screens/PeerToPeer/useTeamPeerToPeerQuery'
import styles from './JoinTeamPeerToPeer.styles.scss'

const JoinTeamPeerToPeer: FC = () => {
  const { id, replace } = useParams()

  const {
    fundraisingExperience: { background_url, landing_page_description, logo_url },
  } = useFundraisingExperienceState()
  const { peerToPeerValue, team, setPeerToPeerState } = usePeerToPeerState()

  const { isLoading, isError } = useTeamPeerToPeerQuery({
    id,
    options: {
      onError: () => {
        triggerToast({
          type: 'error',
          header: 'There was an error loading the page',
          options: {
            onClose: () => replace({ pathname: JOIN_TEAM_PATH }),
          },
        })
      },
      onSuccess: ({
        fundraiser_type,
        team_name,
        team_join_code,
        id,
        avatar_name,
        social_avatar,
        goal_amount,
        amount_raised,
        share_links,
        team_members,
        currency_code,
      }) => {
        setPeerToPeerState({
          ...peerToPeerValue,
          fundraiserType: fundraiser_type,
          avatarName: avatar_name,
          shareLinks: share_links,
          socialAvatar: social_avatar,
          currencyCode: currency_code,
          team: {
            ...team,
            name: team_name,
            joinCode: team_join_code,
            campaignId: id,
            goalAmount: goal_amount,
            //@ts-ignore
            members: team_members,
            amountRaised: amount_raised,
          },
        })
      },
    },
  })

  return (
    <Layout widget={<JoinTeamPeerToPeerWidget isError={isError} isLoading={isLoading} />} image={background_url}>
      <LayoutHeader>
        <img src={logo_url} className='w-20' />
      </LayoutHeader>
      <LayoutContent>
        <SlideAnimation slideInFrom='top' className={styles.text}>
          <HeroAvatar src={background_url} />
        </SlideAnimation>
        <SlideAnimation slideInFrom='bottom' className={styles.text}>
          <Text type='h1'>{team.name}</Text>
          <Text type='h2'>{landing_page_description}</Text>
        </SlideAnimation>
      </LayoutContent>
      <LayoutFooter>
        <PeerToPeerFooter />
      </LayoutFooter>
    </Layout>
  )
}

export { JoinTeamPeerToPeer }
