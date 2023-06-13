import { useRecoilValue } from 'recoil'
import classnames from 'classnames'
import configState from '@/atoms/config'
import { Link, ExternalLinkIcon } from '@/components'
import { Icon } from '@/screens/Layout/Icon'
import { PROFILE_PINNED_ITEMS_PATH } from '@/constants/pathConstants'
import styles from './SidebarPinnedItems.scss'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPencil } from '@fortawesome/pro-regular-svg-icons'

const SidebarPinnedItems = () => {
  const { isGivecloudExpress = false, pinnedMenuItems = [] } = useRecoilValue(configState)
  const hasPinnedMenuItems = pinnedMenuItems?.length > 0

  if (isGivecloudExpress) return null

  return (
    <div className={classnames(styles.root, !hasPinnedMenuItems && styles.empty)}>
      <p className={styles.heading}>Pinned</p>

      {hasPinnedMenuItems && (
        <ul>
          {pinnedMenuItems?.map((pin = {}) => {
            const { key, to = '', url = '', is_external = false, label = '', icon = '' } = pin

            const handleOnPinClick = () => {
              if (label === 'Image Library') {
                window?.j?.images?.show?.()
              } else if (label === 'Downloads Library') {
                window?.j?.downloads?.show()
              }
            }

            return (
              <li key={key}>
                {/* eslint-disable-next-line react/jsx-no-target-blank */}
                <Link
                  href={url}
                  to={to}
                  onClick={handleOnPinClick}
                  target={is_external ? '_blank' : undefined}
                  rel={is_external ? 'noopener noreferrer' : undefined}
                >
                  <div className={styles.iconContainer}>
                    <Icon className={styles.icon} icon={icon} />
                  </div>

                  <span>{label}</span>

                  {is_external && <ExternalLinkIcon className={styles.externalLinkIcon} />}
                </Link>
              </li>
            )
          })}
        </ul>
      )}

      <a className={styles.editLink} href={PROFILE_PINNED_ITEMS_PATH}>
        <FontAwesomeIcon className={styles.editIcon} icon={faPencil} title={faPencil.iconName} />

        <span>Add / Remove Pins</span>
      </a>
    </div>
  )
}

export { SidebarPinnedItems }
