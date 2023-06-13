import classnames from 'classnames'
import PropTypes from 'prop-types'
import { Button } from '@/aerosol'
import { Icon } from '@/screens/Layout/Icon'
import { Dropdown } from '@/screens/Layout/TopBar/components'
import { BOTTOM_END } from '@/shared/constants/popper'
import styles from './TopBarMenuItem.scss'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faChevronDown } from '@fortawesome/pro-regular-svg-icons'

const TopBarMenuItem = ({
  className,
  'data-testid': dataTestid = null,
  icon = '',
  badge = 0,
  label = '',
  children,
  mutedBadge = false,
  onToggleMenu = () => {},
}) => (
  <Dropdown
    data-testid={dataTestid}
    menuPlacement={BOTTOM_END}
    onToggle={onToggleMenu}
    toggleElement={
      <Button className={className} isClean>
        <Icon className='mr-2' icon={icon} />
        {!!label && (
          <span className={styles.label} data-testid='label'>
            {label}
          </span>
        )}

        {badge ? (
          <span className={classnames(styles.badge, mutedBadge ? styles.muted : '')} data-testid='badge'>
            {badge > 10 ? '10+' : badge}
          </span>
        ) : (
          <FontAwesomeIcon title={faChevronDown.iconName} className={styles.icon} icon={faChevronDown} />
        )}
      </Button>
    }
    menuContent={children}
  />
)

TopBarMenuItem.propTypes = {
  className: PropTypes.string,
  'data-testid': PropTypes.string,
  icon: PropTypes.string.isRequired,
  badge: PropTypes.number,
  label: PropTypes.string,
  children: PropTypes.node.isRequired,
  onToggleMenu: PropTypes.func,
  mutedBadge: PropTypes.bool,
}

export { TopBarMenuItem }
