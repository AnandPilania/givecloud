import { useState, Fragment } from 'react'
import { useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import { Popover, Transition } from '@headlessui/react'
import classnames from 'classnames'
import isEmpty from 'lodash/isEmpty'
import { Drawer, Link } from '@/components'
import { SidebarSecondLevelMenu } from '@/screens/Layout/Sidebar/SidebarSecondLevelMenu'
import { SidebarSecondLevelMenuMobile } from '@/screens/Layout/Sidebar/SidebarSecondLevelMenuMobile'
import configState from '@/atoms/config'
import styles from './SidebarFirstLevelMenuItem.scss'

const SidebarFirstLevelMenuItem = ({
  label = '',
  pillClasses = '',
  pillLabel = '',
  flyoutPillClasses = '',
  flyoutPillLabel = '',
  flyoutPillUrl = '',
  flyoutPillIsExternal = false,
  isActiveLink = false,
  isFlyoutOpen = false,
  toggleIsFlyoutOpen = () => null,
  hasOtherOpenMenuItemFlyout = false,
  to = '',
  url = '',
  secondLevelMenuItems = [],
  newLink = {},
  isMobile = false,
  icon = '',
  closeDrawer,
}) => {
  const { isGivecloudExpress } = useRecoilValue(configState)
  const [linkRef, setLinkRef] = useState()
  const hasSecondLevelMenuItems = !isEmpty(secondLevelMenuItems)
  const showSecondLevelMenuDrawer = isMobile && hasSecondLevelMenuItems
  const hideActiveLinkArrow = (isActiveLink && hasOtherOpenMenuItemFlyout) || isMobile
  const hideDesktopActiveLinkStyles = isActiveLink && hasOtherOpenMenuItemFlyout && !isMobile

  const handleOnLinkClick = (e) => {
    !url && e.preventDefault()

    const shouldToggleIsFlyoutOpen = !isMobile

    shouldToggleIsFlyoutOpen && toggleIsFlyoutOpen()

    isMobile && !hasSecondLevelMenuItems && closeDrawer()
  }

  const sidebarSecondLevelMenuProps = {
    icon,
    title: label,
    pillClasses: flyoutPillClasses,
    pillLabel: flyoutPillLabel,
    pillUrl: flyoutPillUrl,
    pillIsExternal: flyoutPillIsExternal,
    newLink,
    menuItems: secondLevelMenuItems,
  }

  return (
    <li
      className={classnames(
        styles.root,
        isActiveLink && styles.activeLink,
        isFlyoutOpen && styles.flyoutOpen,
        hideActiveLinkArrow && styles.hideActiveLinkArrow,
        hideDesktopActiveLinkStyles && styles.hideDesktopActiveLinkStyles
      )}
      aria-haspopup={hasSecondLevelMenuItems}
      aria-expanded={isFlyoutOpen}
    >
      {showSecondLevelMenuDrawer && (
        <Drawer data-testid='drawer' toggleElementRef={linkRef} from='right'>
          <SidebarSecondLevelMenuMobile {...sidebarSecondLevelMenuProps} />
        </Drawer>
      )}

      <Popover as={Fragment}>
        <div className={classnames(pillLabel && styles.linkWithPill)}>
          <Link ref={setLinkRef} href={url} to={to} onClick={handleOnLinkClick} tabIndex='0'>
            {label}
            {pillLabel && (
              <span data-testid='pill' className={classnames(styles.pill, pillClasses)}>
                {pillLabel}
              </span>
            )}
          </Link>
        </div>

        <div className={styles.arrow} data-testid='arrow' />
        <Transition
          className={classnames(styles.flyout, isGivecloudExpress && styles.narrow)}
          show={isFlyoutOpen}
          enter='transition ease-in-out duration-300'
          enterFrom='opacity-0 -translate-x-full'
          enterTo='opacity-100 translate-x-0'
          leave='transition ease-in-out duration-300'
          leaveFrom='opacity-100 translate-x-0'
          leaveTo='opacity-0 -translate-x-full'
        >
          <Popover.Panel className={styles.flyoutPanel} static aria-label='Flyout Panel'>
            <SidebarSecondLevelMenu {...sidebarSecondLevelMenuProps} />
          </Popover.Panel>
        </Transition>
      </Popover>
    </li>
  )
}

SidebarFirstLevelMenuItem.propTypes = {
  label: PropTypes.string.isRequired,
  pillClasses: PropTypes.string,
  pillLabel: PropTypes.string,
  flyoutPillClasses: PropTypes.string,
  flyoutPillLabel: PropTypes.string,
  flyoutPillUrl: PropTypes.string,
  flyoutPillIsExternal: PropTypes.bool,
  isActiveLink: PropTypes.bool,
  isFlyoutOpen: PropTypes.bool,
  toggleIsFlyoutOpen: PropTypes.func,
  hasOtherOpenMenuItemFlyout: PropTypes.bool,
  to: PropTypes.string,
  url: PropTypes.string,
  secondLevelMenuItems: PropTypes.oneOfType([PropTypes.array, PropTypes.object]),
  newLink: PropTypes.shape({
    label: PropTypes.string,
    url: PropTypes.string,
  }),
  isMobile: PropTypes.bool,
  icon: PropTypes.string,
  closeDrawer: PropTypes.func,
}

export { SidebarFirstLevelMenuItem }
