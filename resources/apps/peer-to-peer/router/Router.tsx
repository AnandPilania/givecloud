import type { FC } from 'react'
import { BASE_P2P_PATH } from '@/constants/paths'
import { BrowserRouter as RootRouter } from 'react-router-dom'
import { Switch } from 'react-router-dom'
import { PeerToPeer } from '@/screens/PeerToPeer'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { ToastContainer } from '@/aerosol'

const Router: FC = () => {
  const {
    fundraisingExperience: { id: formId },
  } = useFundraisingExperienceState()

  return (
    <RootRouter basename={`${BASE_P2P_PATH}/${formId}`}>
      <Switch>
        <PeerToPeer />
      </Switch>
      <ToastContainer containerId='app' />
    </RootRouter>
  )
}

export { Router }
