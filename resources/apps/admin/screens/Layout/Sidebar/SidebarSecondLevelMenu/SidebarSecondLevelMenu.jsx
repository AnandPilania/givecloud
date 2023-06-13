import { useState } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { useLocation } from 'react-router-dom'
import isEmpty from 'lodash/isEmpty'
import isArray from 'lodash/isArray'
import { Link, ExternalLinkIcon } from '@/components'
import { Icon } from '@/screens/Layout/Icon'
import { SidebarSecondLevelMenuItem } from '@/screens/Layout/Sidebar/SidebarSecondLevelMenuItem'
import getIsActiveMenuItem from '@/utilities/getIsActiveMenuItem'
import styles from './SidebarSecondLevelMenu.scss'

const buildMenuItems = (
  menuItems = [],
  expandedSubMenuKey = null,
  setExpandedSubMenuKey = () => null,
  location = {}
) => {
  return menuItems.map((menuItem, index) => {
    const {
      key,
      label = '',
      icon = '',
      to = '',
      url = '',
      is_external: isExternal = false,
      children = [],
    } = menuItem || {}

    const isSubMenuExpanded = expandedSubMenuKey === key

    const toggleIsSubMenuExpanded = () => {
      setExpandedSubMenuKey(isSubMenuExpanded ? null : key)
    }

    return (
      <SidebarSecondLevelMenuItem
        key={key || index}
        isActive={getIsActiveMenuItem({ menuItem, location })}
        label={label}
        to={to}
        url={url}
        icon={icon}
        isExternal={isExternal}
        subMenuItems={children}
        isSubMenuExpanded={isSubMenuExpanded}
        toggleIsSubMenuExpanded={toggleIsSubMenuExpanded}
      />
    )
  })
}

const SidebarSecondLevelMenu = ({
  icon = '',
  title = '',
  pillClasses = '',
  pillLabel = '',
  pillUrl = '',
  pillIsExternal = false,
  newLink = {},
  menuItems = [],
}) => {
  const location = useLocation()
  const [expandedSubMenuKey, setExpandedSubMenuKey] = useState(null)

  const hasNewLink = !isEmpty(newLink)
  const hasSections = !isArray(menuItems)

  const makeBuildMenuItems = (menuItems) => {
    return buildMenuItems(menuItems, expandedSubMenuKey, setExpandedSubMenuKey, location)
  }

  return (
    <div className={styles.root}>
      <Icon className={styles.icon} icon={icon} />

      <h3 className='text-2xl'>
        {title}
        {pillLabel && pillUrl ? (
          <Link
            data-testid='pill'
            className={classnames(styles.pill, pillClasses)}
            href={pillUrl}
            target={pillIsExternal ? '_blank' : undefined}
            rel={pillIsExternal ? 'noopener noreferrer' : undefined}
          >
            {pillLabel}
          </Link>
        ) : (
          pillLabel && (
            <span data-testid='pill' className={classnames(styles.pill, pillClasses)}>
              {pillLabel}
            </span>
          )
        )}
      </h3>

      {hasNewLink && (
        // eslint-disable-next-line react/jsx-no-target-blank
        <Link
          className={styles.newLink}
          href={newLink?.url}
          to={newLink?.to}
          onClick={(e) => e.stopPropagation()}
          target={newLink?.is_external ? '_blank' : undefined}
          rel={newLink?.is_external ? 'noopener noreferrer' : undefined}
        >
          <Icon className={styles.newLinkIcon} icon='plus-circle' />

          <span>{newLink?.label}</span>

          {newLink?.is_external && <ExternalLinkIcon className={styles.externalLinkIcon} />}
        </Link>
      )}

      <div className={styles.menu}>
        {hasSections ? (
          Object.keys(menuItems)?.map((sectionKey, index) => {
            const { key = '', label = '', children = [] } = menuItems[sectionKey] || {}

            return (
              <div key={key || index} className={styles.section}>
                {!!label && <p className={styles.sectionHeader}>{label}</p>}

                <ul>{makeBuildMenuItems(children)}</ul>
              </div>
            )
          })
        ) : (
          <ul>{makeBuildMenuItems(menuItems)}</ul>
        )}
      </div>
    </div>
  )
}

SidebarSecondLevelMenu.propTypes = {
  icon: PropTypes.string.isRequired,
  title: PropTypes.string.isRequired,
  pillClasses: PropTypes.string,
  pillLabel: PropTypes.string,
  pillUrl: PropTypes.string,
  pillIsExternal: PropTypes.bool,
  newLink: PropTypes.shape({
    label: PropTypes.string,
    to: PropTypes.string,
    url: PropTypes.string,
  }),
  menuItems: PropTypes.oneOfType([PropTypes.array, PropTypes.object]),
}

export { SidebarSecondLevelMenu }
