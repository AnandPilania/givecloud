import { useState } from 'react'
import { useRecoilValue } from 'recoil'
import axios from 'axios'
import configState from '@/atoms/config'
import { Button } from '@/aerosol/Button'
import { Icon } from '@/screens/Layout/Icon'
import { TopBarMenuItem } from '@/screens/Layout/TopBar/TopBarMenuItem'
import { TopBarMenuUpdates } from '@/screens/Layout/TopBar/TopBarMenuUpdates'
import { TopBarMenuUser } from '@/screens/Layout/TopBar/TopBarMenuUser'
import { TopBarMenuHelp } from '@/screens/Layout/TopBar/TopBarMenuHelp'
import useApiUrl from '@/hooks/api/useApiUrl'
import useTimeOfDay from '@/hooks/useTimeOfDay'
import styles from './TopBarMenu.scss'

const TopBarMenu = () => {
  const apiUrl = useApiUrl()
  const {
    canUserLiveChat = false,
    isGivecloudExpress = false,
    updates = [],
    userFirstName = '',
  } = useRecoilValue(configState)
  const timeOfDay = useTimeOfDay()
  const updatesCount = updates?.filter((update) => update.is_new).length || 0
  const [timeUpdatesChecked] = useState(new Date())
  const [hasSentUpdatesChecked, setHasSentUpdatesChecked] = useState(false)

  const showHelpMenu = !isGivecloudExpress
  const showHelpLiveChat = isGivecloudExpress && canUserLiveChat

  const handleLiveChatClick = (e) => {
    e.preventDefault()
    window?.Intercom?.('showNewMessage')
  }

  const trackUpdateClick = async () => {
    if (!hasSentUpdatesChecked) {
      const response = await axios.post(`${apiUrl}/updates-feed`, {
        last_opened_updates_feed_at: timeUpdatesChecked,
      })
      if (response.status === 204) {
        setHasSentUpdatesChecked(true)
      }
    }
  }

  return (
    <div className={styles.root}>
      <TopBarMenuItem
        onToggleMenu={trackUpdateClick}
        icon='bell'
        badge={updatesCount}
        mutedBadge={hasSentUpdatesChecked ? true : false}
      >
        <TopBarMenuUpdates />
      </TopBarMenuItem>

      {showHelpMenu && (
        <TopBarMenuItem icon='book-open' label='Help'>
          <TopBarMenuHelp />
        </TopBarMenuItem>
      )}

      {showHelpLiveChat && (
        <Button className={styles.liveChatButton} isClean size='small' onClick={handleLiveChatClick}>
          <Icon className='mr-2' icon='book-open' />
          <span className={styles.label}>Help</span>
        </Button>
      )}

      <TopBarMenuItem
        className='mx-2'
        data-testid='topBarMenuUser'
        icon='user-circle'
        label={`${timeOfDay}, ${userFirstName}`}
      >
        <TopBarMenuUser />
      </TopBarMenuItem>
    </div>
  )
}

export { TopBarMenu }
