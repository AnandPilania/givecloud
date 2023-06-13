import PropTypes from 'prop-types'
import { Button } from '@/aerosol'
import { SidebarSecondLevelMenu } from '@/screens/Layout/Sidebar/SidebarSecondLevelMenu'
import { SECONDARY } from '@/shared/constants/theme'
import styles from './SidebarSecondLevelMenuMobile.scss'
import { faTimes } from '@fortawesome/pro-regular-svg-icons'

const SidebarSecondLevelMenuMobile = ({
  title = '',
  newLink = {},
  toggleDrawer = () => null,
  menuItems = [],
  icon = '',
}) => (
  <div className={styles.root}>
    <div className={styles.closeButtonContainer}>
      <Button theme={SECONDARY} icon={faTimes} size='medium' isClean onClick={toggleDrawer} aria-label='Close button' />
    </div>

    <SidebarSecondLevelMenu title={title} newLink={newLink} menuItems={menuItems} icon={icon} />
  </div>
)

SidebarSecondLevelMenuMobile.propTypes = {
  title: PropTypes.string.isRequired,
  newLink: PropTypes.shape({
    label: PropTypes.string,
    url: PropTypes.string,
  }),
  toggleDrawer: PropTypes.func,
  menuItems: PropTypes.oneOfType([PropTypes.array, PropTypes.object]),
  icon: PropTypes.string.isRequired,
}

export { SidebarSecondLevelMenuMobile }
