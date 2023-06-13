import { useRecoilValue } from 'recoil'
import configState from '@/atoms/config'
import { DropdownMenuComponent as DropdownMenu } from '@/screens/Layout/TopBar/components'
import { GIVECLOUD_HELP_URL, TRAINING_VIDEOS_URL, TRUSTRAISING_URL, CALENDLY_URL } from '@/constants/urlConstants'
import styles from './TopBarMenuHelp.scss'

const TopBarMenuHelp = () => {
  const {
    canUserLiveChat = false,
    siteSubscriptionSupportPhone = '',
    siteSubscriptionSupportDirectLine = '',
    isSuperUser = false,
    clientMissionControlUrl = '',
  } = useRecoilValue(configState)

  const showPhoneSupport = siteSubscriptionSupportPhone === 'request' || siteSubscriptionSupportPhone === 'direct'

  const showPhoneSupportDirectLine = siteSubscriptionSupportPhone === 'direct' && !!siteSubscriptionSupportDirectLine

  const handleLiveChatClick = (e) => {
    e.preventDefault()
    window?.Intercom?.('showNewMessage')
  }

  return (
    <DropdownMenu className={styles.root}>
      <li className={styles.header}>Support</li>

      <DropdownMenu.Item label='Help Articles' url={GIVECLOUD_HELP_URL} isExternalLink />
      <DropdownMenu.Item label='Training Videos' url={TRAINING_VIDEOS_URL} isExternalLink />
      <DropdownMenu.Item label='Trustraising' url={TRUSTRAISING_URL} isExternalLink />

      {canUserLiveChat && <DropdownMenu.Item label='Start a Live Chat' onClick={handleLiveChatClick} />}

      {showPhoneSupport && (
        <>
          <DropdownMenu.Divider />

          <li className={styles.header}>Phone Support</li>

          {showPhoneSupportDirectLine && (
            <DropdownMenu.Item
              label={`Toll Free: ${siteSubscriptionSupportDirectLine}`}
              url={`tel:${siteSubscriptionSupportDirectLine}`}
            />
          )}

          <DropdownMenu.Item label='Request a Call' url={CALENDLY_URL} isExternalLink />
        </>
      )}

      {isSuperUser && (
        <>
          <DropdownMenu.Divider />

          <li className={styles.header}>Givecloud Support Team</li>

          <DropdownMenu.Item label='View in MissionControl' url={clientMissionControlUrl} isExternalLink />
        </>
      )}
    </DropdownMenu>
  )
}

export { TopBarMenuHelp }
