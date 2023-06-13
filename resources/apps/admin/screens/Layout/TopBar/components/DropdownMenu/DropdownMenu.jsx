import { memo } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { Icon } from '@/screens/Layout/Icon'
import styles from './DropdownMenu.scss'

export const DropdownMenu = memo(({ className = '', children }) => (
  <ul className={classnames(styles.root, className)}>{children}</ul>
))

DropdownMenu.displayName = 'DropdownMenu'

DropdownMenu.propTypes = {
  className: PropTypes.string,
  children: PropTypes.node.isRequired,
}

const DropdownMenuItem = memo(({ icon = '', label = '', url = '', isExternalLink = false, onClick = () => null }) => (
  <li className={styles.menuItem}>
    {/* eslint-disable-next-line react/jsx-no-target-blank */}
    <a
      className={styles.link}
      href={url || undefined}
      target={isExternalLink ? '_blank' : undefined}
      rel={isExternalLink ? 'noopener noreferrer' : undefined}
      onClick={onClick}
    >
      {!!icon && <Icon className={styles.icon} icon={icon} isFixedWidth />}

      <span>{label}</span>
    </a>
  </li>
))

DropdownMenuItem.displayName = 'DropdownMenuItem'

DropdownMenuItem.propTypes = {
  icon: PropTypes.string,
  label: PropTypes.string,
  url: PropTypes.string,
  isExternalLink: PropTypes.bool,
  onClick: PropTypes.func,
}

const DropdownMenuDivider = memo(() => <li className={styles.divider} />)

DropdownMenuDivider.displayName = 'DropdownMenuDivider'

DropdownMenu.Item = DropdownMenuItem
DropdownMenu.Divider = DropdownMenuDivider

export { DropdownMenu as DropdownMenuComponent }
