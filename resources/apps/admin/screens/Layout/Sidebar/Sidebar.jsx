import PropTypes from 'prop-types'
import classnames from 'classnames'
import { useRecoilValue } from 'recoil'
import { Button } from '@/aerosol/Button'
import { GivecloudLogo } from '@/components/GivecloudLogo'
import { SidebarFirstLevelMenu } from '@/screens/Layout/Sidebar/SidebarFirstLevelMenu'
import { SidebarAlerts } from '@/screens/Layout/Sidebar/SidebarAlerts'
import { SidebarPinnedItems } from '@/screens/Layout/Sidebar/SidebarPinnedItems'
import { ExternalLinkIcon } from '@/components/ExternalLinkIcon/'
import { LIGHT } from '@/shared/constants/theme'
import SupporterSearch from '@/screens/Layout/Sidebar/SupporterSearch'
import styles from './Sidebar.scss'
import configState from '@/atoms/config'
import { faTimes } from '@fortawesome/pro-regular-svg-icons'

const Sidebar = ({ isMobile = false, toggleDrawer = () => null }) => {
  const { isGivecloudExpress, isSupporterSearchEnabled } = useRecoilValue(configState)

  return (
    <div className={classnames(styles.root, isGivecloudExpress && styles.narrow, isMobile && styles.fullScreen)}>
      <div className={styles.scrollContent}>
        <div className={styles.header}>
          <div className={styles.gcLogoContainer} onClick={toggleDrawer} data-testid='logoContainer'>
            <GivecloudLogo className={styles.gcLogo} />
          </div>

          {isMobile && (
            <div className={styles.closeButtonContainer}>
              <Button
                theme={LIGHT}
                icon={faTimes}
                size='medium'
                isClean
                onClick={toggleDrawer}
                aria-label='Close button'
              />
            </div>
          )}
        </div>

        {isSupporterSearchEnabled && <SupporterSearch />}

        <div className={styles.firstLevelMenuContainer}>
          <SidebarFirstLevelMenu isMobile={isMobile} closeDrawer={toggleDrawer} />
        </div>

        <div className={styles.alertsContainer}>
          <SidebarAlerts />
        </div>

        <div className={styles.pinnedItemsContainer}>
          <SidebarPinnedItems />
        </div>
      </div>

      <div className={styles.footer}>
        <a href='/jpanel/feedback'>
          <span>{isGivecloudExpress ? 'We Love Feedback' : 'Have Feedback? Let us know'}</span>

          <ExternalLinkIcon className={styles.externalLinkIcon} />
        </a>
      </div>
    </div>
  )
}

Sidebar.propTypes = {
  isMobile: PropTypes.bool,
  toggleDrawer: PropTypes.func,
}

export { Sidebar }
