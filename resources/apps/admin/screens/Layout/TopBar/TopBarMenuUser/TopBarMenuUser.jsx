import { useRecoilValue } from 'recoil'
import configState from '@/atoms/config'
import { DropdownMenuComponent as DropdownMenu } from '@/screens/Layout/TopBar/components'
import { PROFILE_PATH, SETTINGS_GENERAL_PATH, LOGOUT_PATH } from '@/constants/pathConstants'
import styles from './TopBarMenuUser.scss'

const TopBarMenuUser = () => {
  const {
    userFullName = '',
    clientName = '',
    userEmail = '',
    canUserViewAdmin = false,
    isSuperUser = false,
    localTime = '',
  } = useRecoilValue(configState)

  return (
    <DropdownMenu className={styles.root}>
      <li className={styles.header}>
        <span className={styles.headerText}>{userFullName}</span>
        <span className={styles.headerSubText}>{clientName}</span>
        <span className={styles.headerEmail}>{userEmail}</span>

        {isSuperUser && <span className={styles.headerLocalTime}>{localTime}</span>}
      </li>

      <DropdownMenu.Divider />

      <DropdownMenu.Item icon='user' label='My Profile' url={PROFILE_PATH} />

      {canUserViewAdmin && <DropdownMenu.Item icon='cog' label='Organization Settings' url={SETTINGS_GENERAL_PATH} />}

      <DropdownMenu.Item icon='sign-out' label='Logout' url={LOGOUT_PATH} />
    </DropdownMenu>
  )
}

export { TopBarMenuUser }
