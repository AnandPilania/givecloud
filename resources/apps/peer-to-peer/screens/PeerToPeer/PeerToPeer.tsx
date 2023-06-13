import type { FC } from 'react'
import { useEffect } from 'react'
import { LOGIN_PATH, JOIN_PATH, CREATE_PATH, JOIN_TEAM_PATH } from '@/constants/paths'
import { AuthRoute } from '@/router/AuthRoute'
import { Route } from 'react-router-dom'
import { CreatePeerToPeer } from './CreatePeerToPeer'
import { JoinTeamPeerToPeer } from './JoinTeamPeerToPeer'
import { LoginPeerToPeer } from './LoginPeerToPeer'
import { JoinPeerToPeer } from './JoinPeerToPeer'
import { JoinTeamPeerToPeerFallback } from './JoinTeamPeerToPeerFallback'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'
import { useFundraisingExperienceState } from './useFundraisingExperience'

const PeerToPeer: FC = () => {
  const {
    fundraisingExperience: { primary_colour },
  } = useFundraisingExperienceState()

  useEffect(() => setRootThemeColour({ colour: primary_colour }), [primary_colour])

  return (
    <>
      <Route exact path={LOGIN_PATH} component={LoginPeerToPeer} />
      <AuthRoute path={CREATE_PATH}>
        <CreatePeerToPeer />
      </AuthRoute>
      <Route exact path={JOIN_TEAM_PATH} component={JoinTeamPeerToPeerFallback} />
      <Route exact path={`${JOIN_TEAM_PATH}/:id`} component={JoinTeamPeerToPeer} />
      <AuthRoute exact path={`${JOIN_PATH}/:id`} redirectPath={JOIN_TEAM_PATH}>
        <JoinPeerToPeer />
      </AuthRoute>
    </>
  )
}

export { PeerToPeer }
