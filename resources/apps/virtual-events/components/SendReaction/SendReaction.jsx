import { memo, useState } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import axios from 'axios'
import Emoji from 'react-emoji-render'
import { REACTIONS } from '@/constants/reactionConstants'
import styles from '@/components/SendReaction/SendReaction.scss'

const sendReactions = (pledgeCampaignId, emoji) => {
  axios.get(`/virtual-event/${pledgeCampaignId}/send-reaction?reaction=${emoji}`)
}

const SendReaction = ({ pledgeCampaignId, themeStyle }) => {
  const [isDisabled, setIsDisabled] = useState(false)

  const handleOnReactionClick = (index) => {
    sendReactions(pledgeCampaignId, index)

    setIsDisabled(true)

    setTimeout(() => {
      setIsDisabled(false)
    }, 2000)
  }

  return (
    <div className={`${styles.root} ${themeStyle === 'dark' ? styles.dark : styles.light}`}>
      <div className={styles.heading}>Send a Reaction</div>

      <div className={classnames(styles.reactions, isDisabled && styles.disabled)}>
        {Object.keys(REACTIONS).map((reaction) => (
          <button
            key={reaction}
            className={styles.reactionButton}
            onClick={() => {
              !isDisabled && handleOnReactionClick(reaction)
            }}
          >
            <Emoji text={REACTIONS[reaction]} />
          </button>
        ))}
      </div>
    </div>
  )
}

SendReaction.propTypes = {
  themeStyle: PropTypes.string.isRequired,
  pledgeCampaignId: PropTypes.string.isRequired,
}

export default memo(SendReaction)
