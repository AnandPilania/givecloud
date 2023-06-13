import PropTypes from 'prop-types'
import { GivecloudDefaultPlanPanel } from './GivecloudDefaultPlanPanel'
import { GivecloudExpressPlanPanel } from './GivecloudExpressPlanPanel'

const GivecloudPlanPanel = ({ hasUpgraded }) => {
  if (hasUpgraded) {
    return <GivecloudDefaultPlanPanel />
  }
  return <GivecloudExpressPlanPanel />
}

GivecloudPlanPanel.propTypes = {
  hasUpgraded: PropTypes.bool,
}

export { GivecloudPlanPanel }
