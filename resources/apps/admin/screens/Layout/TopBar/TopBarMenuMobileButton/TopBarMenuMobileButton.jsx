import { Dropdown } from '@/screens/Layout/TopBar/components'
import { TopBarMenu } from '@/screens/Layout/TopBar/TopBarMenu'
import { BOTTOM_END } from '@/shared/constants/popper'
import styles from './TopBarMenuMobileButton.scss'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faEllipsisV } from '@fortawesome/pro-regular-svg-icons'

const TopBarMenuMobileButton = () => (
  <div className={styles.root}>
    <Dropdown
      menuPlacement={BOTTOM_END}
      toggleElement={
        <button className={styles.menuButton}>
          <FontAwesomeIcon icon={faEllipsisV} />
        </button>
      }
      menuContent={<TopBarMenu />}
    />
  </div>
)

export { TopBarMenuMobileButton }
