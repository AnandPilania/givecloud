import { useState } from 'react'
import { useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import { useLocation } from 'react-router-dom'
import configState from '@/atoms/config'
import { SidebarFirstLevelMenuItem } from '@/screens/Layout/Sidebar/SidebarFirstLevelMenuItem'
import getIsActiveMenuItem from '@/utilities/getIsActiveMenuItem'
import { useOnClickOutside } from '@/shared/hooks'
import styles from './SidebarFirstLevelMenu.scss'

const SidebarFirstLevelMenu = ({ isMobile = false, closeDrawer }) => {
  const { uiFeaturePreviewMenuItems = [] } = useRecoilValue(configState)
  const location = useLocation()
  const [listRef, setListRef] = useState(false)
  const [openMenuItemFlyoutKey, setOpenMenuItemFlyoutKey] = useState(null)

  const handleOnClickOutside = () => setOpenMenuItemFlyoutKey(null)

  useOnClickOutside({ ref: listRef, onClickOutside: handleOnClickOutside })

  return (
    <ul ref={setListRef} className={styles.root}>
      {uiFeaturePreviewMenuItems?.map((menuItem) => {
        const {
          key = '',
          label = '',
          pill_classes = '',
          pill_label = '',
          flyout_pill_classes = '',
          flyout_pill_label = '',
          flyout_pill_url = '',
          flyout_pill_is_external = false,
          to = '',
          url = '',
          new_link: newLink = {},
          children: secondLevelMenuItems = [],
          icon = '',
        } = menuItem || {}

        const isFlyoutOpen = openMenuItemFlyoutKey === key
        const hasOtherOpenMenuItemFlyout = !!openMenuItemFlyoutKey && !isFlyoutOpen
        const menuItemHasNoChildren = !Object.keys(secondLevelMenuItems).length

        const toggleIsFlyoutOpen = () => {
          setOpenMenuItemFlyoutKey(menuItemHasNoChildren || isFlyoutOpen ? null : key)
        }

        return (
          <SidebarFirstLevelMenuItem
            key={key}
            label={label}
            pillClasses={pill_classes}
            pillLabel={pill_label}
            flyoutPillClasses={flyout_pill_classes}
            flyoutPillLabel={flyout_pill_label}
            flyoutPillUrl={flyout_pill_url}
            flyoutPillIsExternal={flyout_pill_is_external}
            isActiveLink={getIsActiveMenuItem({ menuItem, location })}
            isFlyoutOpen={isFlyoutOpen}
            toggleIsFlyoutOpen={toggleIsFlyoutOpen}
            hasOtherOpenMenuItemFlyout={hasOtherOpenMenuItemFlyout}
            to={to}
            url={url}
            newLink={newLink}
            secondLevelMenuItems={secondLevelMenuItems}
            isMobile={isMobile}
            icon={icon}
            closeDrawer={closeDrawer}
          />
        )
      })}
    </ul>
  )
}

SidebarFirstLevelMenu.propTypes = {
  isMobile: PropTypes.bool,
  closeDrawer: PropTypes.func,
}

export { SidebarFirstLevelMenu }
