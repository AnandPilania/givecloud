import { memo, useState, useEffect } from 'react'
import PropTypes from 'prop-types'
import axios from 'axios'
import Pusher from 'pusher-js'
import faker from 'faker'
import classnames from 'classnames'
import { useEffectOnce } from 'react-use'
import Logo from '@/components/Logo/Logo'
import Video from '@/components/Video/Video'
import Updates from '@/components/Updates/Updates'
import Comments from '@/components/Comments/Comments'
import Confetti from '@/components/Confetti/Confetti'
import TakeAction from '@/components/TakeAction/TakeAction'
import GCLogo from '@/components/GCLogo/GCLogo'
import EmojiReactions from '@/components/EmojiReactions/EmojiReactions'
import SendReaction from '@/components/SendReaction/SendReaction'
import IncompatibleOS from '@/components/IncompatibleOS/IncompatibleOS'
import isCompatibleOS from '@/utilities/isCompatibleOS'
import { REACTIONS } from '@/constants/reactionConstants'
import { VIDEO_PROVIDERS } from '@/constants/videoProviderConstants'
import styles from '@/screens/Landing/Landing.scss'
import classNames from 'classnames'

const Landing = ({
  initialPageTitle,
  initialLogo,
  initialBgImage,
  initialThemeStyle,
  initialThemePrimaryColor,
  type = 'full',
  currency,
  initialIsChatEnabled,
  initialIsHonorRollEnabled,
  initialIsEmojiReactionEnabled,
  initialIsCelebrationEnabled,
  initialCelebrationThreshold,
  initialIsAmountTallyEnabled,
  pusherConfig,
  pusherChannel,
  pledgeCampaignId,
  initialTabs,
  initialVideoId,
  initialChatId,
  initialVideoProvider,
  domain,
  isDemoModeEnabled,
  updatesTotalText,
  updatesTotalTextColour,
  initialLiveStreamStatus,
  initialPrestreamMessageLine1,
  initialPrestreamMessageLine2,
}) => {
  const [pageTitle, setPageTitle] = useState(initialPageTitle)
  const [logo, setLogo] = useState(initialLogo)
  const [bgImage, setBgImage] = useState(initialBgImage)
  const [themeStyle, setThemeStyle] = useState(initialThemeStyle)
  const [themePrimaryColor, setThemePrimaryColor] = useState(initialThemePrimaryColor)
  const [showCelebration, setShowCelebration] = useState(false)
  const [isAmountTallyEnabled, setIsAmountTallyEnabled] = useState(initialIsAmountTallyEnabled)
  const [isChatEnabled, setIsChatEnabled] = useState(initialIsChatEnabled)
  const [isCelebrationEnabled, setIsCelebrationEnabled] = useState(initialIsCelebrationEnabled)
  const [isHonorRollEnabled, setIsHonorRollEnabled] = useState(initialIsHonorRollEnabled)
  const [isEmojiReactionEnabled, setIsEmojiReactionEnabled] = useState(initialIsEmojiReactionEnabled)
  const [celebrationThreshold, setCelebrationThreshold] = useState(initialCelebrationThreshold)
  const [liveStreamStatus, setLiveStreamStatus] = useState(initialLiveStreamStatus)
  const [prestreamMessageLine1, setPrestreamMessageLine1] = useState(initialPrestreamMessageLine1)
  const [prestreamMessageLine2, setPrestreamMessageLine2] = useState(initialPrestreamMessageLine2)
  const [tabs, setTabs] = useState(initialTabs)
  const [videoProvider, setVideoProvider] = useState(initialVideoProvider)
  const [videoId, setVideoId] = useState(initialVideoId)
  const [chatId, setChatId] = useState(initialChatId)
  const [donors, setDonors] = useState([])
  const [donationTotal, setDonationTotal] = useState(0)
  const [donorToCheckForCelebration, setDonorToCheckForCelebration] = useState(null)
  const [isInitialized, setIsInitialized] = useState(false)
  const [iframeKey, setIframeKey] = useState(0)
  const [reactions, setReactions] = useState({})
  const compatibleBrowserOS = isCompatibleOS()
  const hasTabs = tabs.length > 0 ? true : false

  const onNewDonationOrPledge = (data) => {
    setDonors((donors) => [data.pledgable, ...donors])
    setDonationTotal((donationTotal) => {
      // incoming Pusher events are asynchronous and the order in which events
      // are received is not guaranteed so check to make sure total is going up
      return Math.max(donationTotal, data.campaign.pledgable_total_amount)
    })
    setDonorToCheckForCelebration(data.pledgable)
  }

  useEffect(() => {
    if (donorToCheckForCelebration) {
      if (donorToCheckForCelebration.amount >= celebrationThreshold && isCelebrationEnabled) {
        setShowCelebration(true)
      }
      setDonorToCheckForCelebration(null)
    }
  }, [donorToCheckForCelebration, celebrationThreshold, isCelebrationEnabled])

  const onAmountRefresh = (data) => {
    setIsInitialized(true)
    setDonors((donors) => (donors.length ? donors : data.pledgeables.reverse()))

    setDonationTotal((total) => (total == '' ? data.campaign.pledgable_total_amount : total))
  }
  const onAmountRollback = (data) => {
    setDonors((donors) => donors.filter((donor) => donor.id !== data.pledgable.id))
    setDonationTotal(data.campaign.pledgable_total_amount)
  }

  useEffect(() => {
    document.title = pageTitle
  }, [pageTitle])

  useEffect(() => {
    setInterval(() => {
      setReactions((reactions) => {
        const TWENTY_SECONDS = 20 * 1000

        Object.keys(reactions).map((index) => {
          const reaction = reactions[index]
          // only keep reactions in the object if they are less than 30 seconds old
          if (new Date() - reaction.dateAdded > TWENTY_SECONDS) {
            delete reactions[index]
          }
        })
        return reactions
      })
    }, 10000)
  }, [])

  useEffect(() => {
    if (isDemoModeEnabled) {
      const timeout = setTimeout(() => {
        const amount = parseInt(Math.random() * 1500, 10)

        onNewDonationOrPledge({
          pledgable: {
            amount,
            comment: null,
            currency,
            id: faker.random.number(),
            location: faker.address.state(),
            name: faker.name.findName(),
            type: 'purchase',
            date: new Date(),
          },
          campaign: {
            pledgable_total_amount: donationTotal + amount,
          },
        })
      }, parseInt(Math.random() * 5000, 10) + 1000)

      return () => {
        clearTimeout(timeout)
      }
    }
  }, [donationTotal, currency, isDemoModeEnabled])

  useEffect(() => {
    if (isDemoModeEnabled) {
      const interval = setInterval(() => {
        const keys = Object.keys(REACTIONS)
        const emoji = keys[(keys.length * Math.random()) << 0]

        onReaction({ reaction: emoji })
      }, 500)

      return () => {
        clearInterval(interval)
      }
    }
  }, [isDemoModeEnabled])

  const onReaction = (data) => {
    const classes = [styles.floatUp1, styles.floatUp2]
    const className = classes[Math.floor(Math.random() * classes.length)]
    const left = parseInt(Math.random() * 100, 10)
    const randomIndex = parseInt(Math.random() * 10000000, 0)
    const dateAdded = new Date()

    if (REACTIONS[data.reaction]) {
      setReactions((reactions) => {
        // never allow more than 50 on the page
        if (Object.keys(reactions).length > 50) {
          return reactions
        }

        return {
          ...reactions,
          [randomIndex]: {
            emoji: data.reaction,
            className: className,
            left: left,
            dateAdded: dateAdded,
          },
        }
      })
    }
  }

  const onConfigurationUpdate = (data) => {
    setPageTitle(data.event.name)
    setLogo(data.event.logo)
    setBgImage(data.event.background_image)
    setThemeStyle(data.event.theme_style)
    setThemePrimaryColor(data.event.theme_primary_color)
    setIsChatEnabled(data.event.is_chat_enabled)
    setIsCelebrationEnabled(data.event.is_celebration_enabled)
    setIsHonorRollEnabled(data.event.is_honor_roll_enabled)
    setIsEmojiReactionEnabled(data.event.is_emoji_reaction_enabled)
    setIsAmountTallyEnabled(data.event.is_amount_tally_enabled)
    setCelebrationThreshold(data.event.celebration_threshold)
    setVideoProvider(data.event.video_source)
    setVideoId(data.event.video_id)
    setChatId(data.event.chat_id)
    setLiveStreamStatus(data.event.live_stream_status)
    setPrestreamMessageLine1(data.event.prestream_message_line_1)
    setPrestreamMessageLine2(data.event.prestream_message_line_2)

    const newTabs = []
    if (data.event.tab_one_label && data.event.tab_one_product_id) {
      newTabs.push({
        name: data.event.tab_one_label,
        productId: data.event.tab_one_product_id,
      })
    }
    if (data.event.tab_two_label && data.event.tab_two_product_id) {
      newTabs.push({
        name: data.event.tab_two_label,
        productId: data.event.tab_two_product_id,
      })
    }
    if (data.event.tab_three_label && data.event.tab_three_product_id) {
      newTabs.push({
        name: data.event.tab_three_label,
        productId: data.event.tab_three_product_id,
      })
    }
    setTabs(newTabs)
  }

  useEffectOnce(() => {
    const pusher = new Pusher(pusherConfig.key, pusherConfig)

    const channel = pusher.subscribe(pusherChannel)
    channel.bind('pledgable_amount_committed', onNewDonationOrPledge)
    channel.bind('pledgable_amounts_refresh', onAmountRefresh)
    channel.bind('pledgable_amount_rollback', onAmountRollback)
    channel.bind('reaction', onReaction)
    channel.bind('configuration_update', onConfigurationUpdate)
    channel.bind('force_reload', () => window.location.reload())
    channel.bind('force_iframe_reload', () => {
      // this force refreshes the take action panel
      setIframeKey(Math.random())
    })

    setTimeout(() => {
      const refreshURL = `https://${domain}/gc-json/v1/pledge-campaigns/${pledgeCampaignId}/refresh`
      axios.get(refreshURL)
    }, 3000)
  })

  return (
    <div className={styles.root}>
      <div className={styles.background} style={{ backgroundImage: `url(${bgImage})` }} />

      <div className={classNames(styles.overlay, themeStyle == 'dark' ? styles.dark : styles.light)} />

      {showCelebration && (
        <Confetti
          onConfettiComplete={() => {
            setShowCelebration(false)
          }}
        />
      )}

      <div className={styles.main}>
        <div className={styles.content}>
          <div className={classnames(styles.logoContainer, type === 'full' && styles.full)}>
            <Logo logo={logo} />
          </div>

          <div className={styles.contentInner}>
            {!compatibleBrowserOS && (
              <IncompatibleOS
                themeStyle={themeStyle}
                themePrimaryColor={themePrimaryColor}
                videoProvider={videoProvider}
                videoId={videoId}
              />
            )}

            {compatibleBrowserOS && (
              <>
                {type === 'dashboard' && (
                  <Updates
                    themeStyle={themeStyle}
                    themePrimaryColor={themePrimaryColor}
                    isAmountTallyEnabled={isAmountTallyEnabled}
                    isHonorRollEnabled={isHonorRollEnabled}
                    donationTotal={donationTotal}
                    isInitialized={isInitialized}
                    donors={donors}
                    type={type}
                    celebrationThreshold={celebrationThreshold}
                    totalText={updatesTotalText}
                    totalTextColour={updatesTotalTextColour}
                  />
                )}

                {type === 'full' && (
                  <>
                    <div className={styles.columnOne}>
                      <Video
                        themeStyle={themeStyle}
                        videoProvider={videoProvider}
                        videoId={videoId}
                        liveStreamStatus={liveStreamStatus}
                        prestreamMessageLine1={prestreamMessageLine1}
                        prestreamMessageLine2={prestreamMessageLine2}
                      />

                      {!!isEmojiReactionEnabled && (
                        <div className={styles.sendReactionContainer}>
                          <SendReaction themeStyle={themeStyle} pledgeCampaignId={pledgeCampaignId} />
                        </div>
                      )}

                      <div className={styles.updatesContainer}>
                        <Updates
                          themeStyle={themeStyle}
                          themePrimaryColor={themePrimaryColor}
                          isAmountTallyEnabled={isAmountTallyEnabled}
                          isHonorRollEnabled={isHonorRollEnabled}
                          donationTotal={donationTotal}
                          isInitialized={isInitialized}
                          donors={donors}
                          type={type}
                          celebrationThreshold={celebrationThreshold}
                          totalText={updatesTotalText}
                          totalTextColour={updatesTotalTextColour}
                        />
                      </div>
                    </div>

                    <div className={styles.columnTwo}>
                      {hasTabs && (
                        <TakeAction
                          themeStyle={themeStyle}
                          themePrimaryColor={themePrimaryColor}
                          key={iframeKey}
                          domain={domain}
                          tabs={tabs}
                        />
                      )}

                      <div className={styles.updatesContainer}>
                        <Updates
                          themeStyle={themeStyle}
                          themePrimaryColor={themePrimaryColor}
                          isAmountTallyEnabled={isAmountTallyEnabled}
                          isHonorRollEnabled={isHonorRollEnabled}
                          donationTotal={donationTotal}
                          isInitialized={isInitialized}
                          donors={donors}
                          type={type}
                          celebrationThreshold={celebrationThreshold}
                          totalText={updatesTotalText}
                          totalTextColour={updatesTotalTextColour}
                        />
                      </div>

                      {!!isEmojiReactionEnabled && (
                        <div className={styles.sendReactionContainer}>
                          <SendReaction themeStyle={themeStyle} pledgeCampaignId={pledgeCampaignId} />
                        </div>
                      )}

                      <div className={styles.chatContainer}>
                        {!!isChatEnabled && (
                          <Comments
                            themeStyle={themeStyle}
                            videoProvider={videoProvider}
                            videoId={videoId}
                            chatId={chatId}
                            domain={domain}
                          />
                        )}
                      </div>
                    </div>
                  </>
                )}
              </>
            )}
          </div>
        </div>

        <GCLogo />

        <div className={styles.emojiReactionsContainer}>
          <EmojiReactions reactions={reactions} />
        </div>
      </div>
    </div>
  )
}

Landing.propTypes = {
  initialPageTitle: PropTypes.string,
  initialLogo: PropTypes.string,
  initialBgImage: PropTypes.string,
  initialThemeStyle: PropTypes.string,
  initialThemePrimaryColor: PropTypes.string,
  type: PropTypes.string,
  currency: PropTypes.object,
  initialIsChatEnabled: PropTypes.oneOf([0, 1]),
  initialIsHonorRollEnabled: PropTypes.oneOf([0, 1]),
  initialIsEmojiReactionEnabled: PropTypes.oneOf([0, 1]),
  initialIsCelebrationEnabled: PropTypes.oneOf([0, 1]),
  initialCelebrationThreshold: PropTypes.string,
  initialIsAmountTallyEnabled: PropTypes.oneOf([0, 1]),
  pusherConfig: PropTypes.shape({
    key: PropTypes.string,
  }),
  pusherChannel: PropTypes.string,
  pledgeCampaignId: PropTypes.string,
  initialTabs: PropTypes.arrayOf(
    PropTypes.shape({
      name: PropTypes.string,
      productId: PropTypes.string,
    })
  ),
  initialVideoId: PropTypes.string,
  initialChatId: PropTypes.string,
  initialVideoProvider: PropTypes.oneOf(VIDEO_PROVIDERS),
  domain: PropTypes.string,
  isDemoModeEnabled: PropTypes.oneOf([0, 1]),
  updatesTotalText: PropTypes.string,
  updatesTotalTextColour: PropTypes.string,
  initialLiveStreamStatus: PropTypes.string,
  initialPrestreamMessageLine1: PropTypes.string,
  initialPrestreamMessageLine2: PropTypes.string,
}

export default memo(Landing)
