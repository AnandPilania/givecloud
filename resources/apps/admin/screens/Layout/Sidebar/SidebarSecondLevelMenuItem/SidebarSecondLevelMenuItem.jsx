import PropTypes from 'prop-types'
import { useEffectOnce } from 'react-use'
import { useLocation } from 'react-router-dom'
import classnames from 'classnames'
import { Expandable, Link, ExternalLinkIcon } from '@/components'
import { Icon } from '@/screens/Layout/Icon'
import getIsActiveMenuItem from '@/utilities/getIsActiveMenuItem'
import styles from './SidebarSecondLevelMenuItem.scss'

const SidebarSecondLevelMenuItem = ({
  isActive = false,
  label = '',
  to = '',
  url = '',
  icon = '',
  isExternal = false,
  subMenuItems = [],
  isSubMenuExpanded = false,
  toggleIsSubMenuExpanded = () => null,
}) => {
  const location = useLocation()
  const hasSubMenu = !!subMenuItems?.length > 0

  const handleOnLinkClick = () => {
    hasSubMenu && toggleIsSubMenuExpanded()
  }

  useEffectOnce(() => {
    hasSubMenu && isActive && toggleIsSubMenuExpanded()
  })

  return (
    <li className={classnames(styles.root, isActive && styles.active, isSubMenuExpanded && styles.subMenuExpanded)}>
      <Link
        className={classnames(styles.link, url && styles.hover)}
        href={url}
        to={to}
        onClick={handleOnLinkClick}
        target={isExternal ? '_blank' : undefined}
        rel={isExternal ? 'noopener noreferrer' : undefined}
      >
        {isSubMenuExpanded && <Icon className={styles.labelIcon} icon={icon} />}

        <span>{label}</span>

        {hasSubMenu && <Icon className={styles.expandIcon} icon='chevron-down' />}
      </Link>

      {hasSubMenu && (
        <Expandable isExpanded={isSubMenuExpanded}>
          <ul className={styles.subMenuList}>
            {subMenuItems.map((subMenuItem, index) => {
              const { key = '', label = '', to = '', url = '', is_external: isExternal = false } = subMenuItem || {}

              const isActive = getIsActiveMenuItem({ menuItem: subMenuItem, location })
              const showAsLink = url || label === 'Image Library' || label === 'Downloads Library'

              const handleOnLinkClick = () => {
                if (label === 'Image Library') {
                  window?.j?.images?.showInIframe?.()
                } else if (label === 'Downloads Library') {
                  window?.j?.downloads?.showInIframe?.()
                }
              }

              return (
                <li key={key || index} className={classnames(styles.subMenuListItem, isActive && styles.active)}>
                  {/* eslint-disable-next-line react/jsx-no-target-blank */}
                  <Link
                    className={classnames(showAsLink && styles.link)}
                    href={url}
                    to={to}
                    onClick={handleOnLinkClick}
                    target={isExternal ? '_blank' : undefined}
                    rel={isExternal ? 'noopener noreferrer' : undefined}
                  >
                    <span>{label}</span>

                    {isExternal && <ExternalLinkIcon className={styles.externalLinkIcon} />}
                  </Link>
                </li>
              )
            })}
          </ul>
        </Expandable>
      )}
    </li>
  )
}

SidebarSecondLevelMenuItem.propTypes = {
  isActive: PropTypes.bool,
  label: PropTypes.string.isRequired,
  to: PropTypes.string,
  url: PropTypes.string,
  icon: PropTypes.string,
  isExternal: PropTypes.bool,
  subMenuItems: PropTypes.array,
  isSubMenuExpanded: PropTypes.bool,
  toggleIsSubMenuExpanded: PropTypes.func,
}

export { SidebarSecondLevelMenuItem }
