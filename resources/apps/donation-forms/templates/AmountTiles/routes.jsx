import { Route } from 'react-router-dom'
import { LANDING, CHOOSE_PAYMENT_METHOD } from '@/constants/pathConstants'
import ChoosePaymentMethod from './screens/ChoosePaymentMethod/ChoosePaymentMethod'
import { getDefaultPath } from '@/utilities/getDefaultPath'

const layoutPaths = { standard: LANDING, simplified: CHOOSE_PAYMENT_METHOD }
const desktopLayoutPaths = { standard: CHOOSE_PAYMENT_METHOD, simplified: CHOOSE_PAYMENT_METHOD }

const routes = {
  DEFAULT_PATH_MOBILE: getDefaultPath(layoutPaths),
  DEFAULT_PATH_DESKTOP: getDefaultPath(desktopLayoutPaths),

  routes: () => [
    <Route key={CHOOSE_PAYMENT_METHOD} exact path={CHOOSE_PAYMENT_METHOD} component={ChoosePaymentMethod} />,
  ],
}

export default routes
