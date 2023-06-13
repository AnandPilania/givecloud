import { Route, Switch } from 'react-router-dom'
import Checkout from '@/screens/Checkout/Checkout'
import MonthlyUpsell from '@/screens/MonthlyUpsell/MonthlyUpsell'
import DoubleTheDonationSearch from '@/screens/DoubleTheDonationSearch/DoubleTheDonationSearch'
import DoubleTheDonationMatch from '@/screens/DoubleTheDonationMatch/DoubleTheDonationMatch'
import EmailOptIn from '@/screens/EmailOptIn/EmailOptIn'
import ThankYou from '@/screens/ThankYou/ThankYou'
import Landing from '@/screens/Landing/Landing'
import { templateRoutes } from '@/templates'
import * as paths from '@/constants/pathConstants'

const routes = (location) => (
  <Switch location={location} key={location.pathname}>
    {templateRoutes.routes().map((route) => route)}

    <Route exact path={paths.LANDING} component={Landing} />
    <Route exact path={paths.CHECKOUT} component={Checkout} />
    <Route exact path={paths.MONTHLY_UPSELL} component={MonthlyUpsell} />
    <Route exact path={paths.DOUBLE_THE_DONATION_SEARCH} component={DoubleTheDonationSearch} />
    <Route exact path={paths.DOUBLE_THE_DONATION_MATCH} component={DoubleTheDonationMatch} />
    <Route exact path={paths.EMAIL_OPT_IN} component={EmailOptIn} />
    <Route exact path={paths.THANK_YOU} component={ThankYou} />
  </Switch>
)

export const DEFAULT_PATH = { desktop: templateRoutes.DEFAULT_PATH_DESKTOP, mobile: templateRoutes.DEFAULT_PATH_MOBILE }

export default routes
