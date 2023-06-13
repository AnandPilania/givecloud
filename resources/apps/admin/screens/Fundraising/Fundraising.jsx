import { Switch, Route } from 'react-router-dom'
import { FundraisingFormDashboard } from '@/screens/Fundraising/FundraisingFormDashboard'
import { FundraisingForms } from '@/screens/Fundraising/FundraisingForms'
import { DeletedFundraisingForms } from '@/screens/Fundraising/DeletedFundraisingForms'

const Fundraising = () => (
  <Switch>
    <Route path='/fundraising/forms/' exact component={FundraisingForms} />
    <Route path='/fundraising/forms/deleted-forms' exact component={DeletedFundraisingForms} />
    <Route path='/fundraising/forms/:id' exact component={FundraisingFormDashboard} />
  </Switch>
)

export default Fundraising
