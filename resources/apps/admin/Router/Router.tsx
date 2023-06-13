import { Switch, Route } from 'react-router-dom'
import { BrowserRouter as RootRouter } from 'react-router-dom'
import { BASE_ADMIN_PATH } from '@/constants/pathConstants'
import { useRecoilValue } from 'recoil'
import useTrackPageVisit from '@/hooks/useTrackPageVisit'
import appSourceState from '@/atoms/appSource'
import configState from '@/atoms/config'
import Dashboard from '@/screens/Dashboard'
import Feedback from '@/screens/Feedback'
import Fundraising from '@/screens/Fundraising'
import Imports from '@/screens/Imports'
import { Layout } from '@/screens/Layout'
import { OrgSettings } from '@/screens/OrgSettings'
import { PageNotFound } from '@/screens/PageNotFound'
import { ToastContainer } from '@/aerosol'

const Router = () => {
  const appSource = useRecoilValue(appSourceState)
  const isAppSourceSpa = appSource === 'SPA'
  const { isFundraisingFormsEnabled } = useRecoilValue(configState)

  useTrackPageVisit()

  const renderFundraisingRoutes = () => {
    if (isFundraisingFormsEnabled) {
      return <Route path='/fundraising/forms' component={Fundraising} />
    }
    return null
  }

  const renderPageNotFoundRoute = () => {
    if (isAppSourceSpa) {
      return (
        <Route>
          <PageNotFound />
        </Route>
      )
    }
    return null
  }

  return (
    <RootRouter basename={BASE_ADMIN_PATH}>
      <Layout>
        <Switch>
          <Route path='/' exact={true} component={Dashboard} />
          <Route path='/imports/wizard/:id' component={Imports} />
          <Route path='/feedback'>
            <Route path='/' exact={true} component={Feedback} />
            <Route path='*' component={Feedback} />
          </Route>
          <Route path='/settings/general' component={OrgSettings} />
          {renderFundraisingRoutes()}
          {renderPageNotFoundRoute()}
        </Switch>
      </Layout>
      <ToastContainer containerId='app' />
    </RootRouter>
  )
}

export { Router }
