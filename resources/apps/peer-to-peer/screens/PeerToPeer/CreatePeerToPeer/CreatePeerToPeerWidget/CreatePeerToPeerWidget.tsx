import type { FC } from 'react'
import { useEffect } from 'react'
import { Route, Switch, useRouteMatch } from 'react-router-dom'
import { Widget } from '@/components'
import { SelectionScreen } from './SelectionScreen'
import { Personal } from './Personal'
import { Team } from './Team'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import { useSupporterState } from '@/screens/PeerToPeer/useSupporterState'
import { FAQDrawer } from '@/screens/PeerToPeer/FAQDrawer'
import { PrivacyDrawer } from '@/screens/PeerToPeer/PrivacyDrawer'
import { CREATE_PERSONAL_PATH, CREATE_TEAM_PATH } from '@/constants/paths'

const CreatePeerToPeerWidget: FC = () => {
  const { path } = useRouteMatch()
  const { supporter } = useSupporterState()
  const { peerToPeerValue, team, setPeerToPeerState } = usePeerToPeerState()

  useEffect(() => {
    const name = supporter.first_name ? `${supporter.first_name}'s Team` : ''

    setPeerToPeerState({
      ...peerToPeerValue,
      socialAvatar: supporter.avatar,
      supporterInitials: `${supporter.first_name.charAt(0)}${supporter.last_name.charAt(0)}`,
      team: {
        ...team,
        name,
      },
    })
  }, [])

  return (
    <Widget>
      <Switch>
        <Route exact path={path} component={SelectionScreen} />
        <Route path={CREATE_TEAM_PATH} component={Team} />
        <Route path={CREATE_PERSONAL_PATH} component={Personal} />
      </Switch>
      <FAQDrawer />
      <PrivacyDrawer />
    </Widget>
  )
}

export { CreatePeerToPeerWidget }
