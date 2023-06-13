import { memo } from 'react'
import Landing from '@/screens/Landing/Landing'
import styles from '@/components/App/App.scss'

const el = document.getElementById('app')

const pageTitle = el.getAttribute('data-page-title')
const logo = el.getAttribute('data-logo')
const bgImage = el.getAttribute('data-bg-image')
const themeStyle = el.getAttribute('data-theme-style')
const themePrimaryColor = el.getAttribute('data-theme-primary-color')
const eventCode = el.getAttribute('data-event-code')
const type = el.getAttribute('data-type')
const currency = JSON.parse(el.getAttribute('data-currency'))
const isAmountTallyEnabled = JSON.parse(el.getAttribute('data-is-amount-tally-enabled'))
const isChatEnabled = JSON.parse(el.getAttribute('data-is-chat-enabled'))
const isCelebrationEnabled = JSON.parse(el.getAttribute('data-is-celebration-enabled'))
const isHonorRollEnabled = JSON.parse(el.getAttribute('data-is-honor-roll-enabled'))
const isEmojiReactionEnabled = JSON.parse(el.getAttribute('data-is-emoji-reaction-enabled'))
const celebrationThreshold = el.getAttribute('data-celebration-threshold')
const pusherConfig = JSON.parse(el.getAttribute('data-pusher-config'))
const pusherChannel = el.getAttribute('data-pusher-channel')
const pledgeCampaignId = el.getAttribute('data-pledge-campaign-id')
const tabs = JSON.parse(el.getAttribute('data-tabs'))
const videoId = el.getAttribute('data-video-id')
const chatId = el.getAttribute('data-chat-id')
const videoProvider = el.getAttribute('data-video-provider')
const domain = el.getAttribute('data-domain')
const isDemoModeEnabled = JSON.parse(el.getAttribute('data-is-demo-mode-enabled'))
const updatesTotalText = el.getAttribute('data-updates-total-text')
const updatesTotalTextColour = el.getAttribute('data-updates-total-text-colour')
const liveStreamStatus = el.getAttribute('data-live-stream-status')
const prestreamMessageLine1 = el.getAttribute('data-prestream-message-line-one')
const prestreamMessageLine2 = el.getAttribute('data-prestream-message-line-two')

const App = () => (
  <div className={styles.root}>
    <Landing
      initialPageTitle={pageTitle}
      initialLogo={logo}
      initialBgImage={bgImage}
      initialThemeStyle={themeStyle}
      initialThemePrimaryColor={themePrimaryColor}
      eventCode={eventCode}
      type={type}
      currency={currency}
      initialIsAmountTallyEnabled={isAmountTallyEnabled}
      initialCelebrationThreshold={celebrationThreshold}
      initialIsCelebrationEnabled={isCelebrationEnabled}
      initialIsHonorRollEnabled={isHonorRollEnabled}
      initialIsEmojiReactionEnabled={isEmojiReactionEnabled}
      initialIsChatEnabled={isChatEnabled}
      pusherConfig={pusherConfig}
      pusherChannel={pusherChannel}
      pledgeCampaignId={pledgeCampaignId}
      initialTabs={tabs}
      initialVideoId={videoId}
      initialChatId={chatId}
      initialVideoProvider={videoProvider}
      domain={domain}
      isDemoModeEnabled={isDemoModeEnabled}
      updatesTotalText={updatesTotalText}
      updatesTotalTextColour={updatesTotalTextColour}
      initialLiveStreamStatus={liveStreamStatus}
      initialPrestreamMessageLine1={prestreamMessageLine1}
      initialPrestreamMessageLine2={prestreamMessageLine2}
    />
  </div>
)

export default memo(App)
