import { memo } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import Emoji from 'react-emoji-render'
import { REACTIONS } from '@/constants/reactionConstants'
import styles from '@/components/EmojiReactions/EmojiReactions.scss'

const EmojiReactions = ({ reactions }) => (
  <div className={styles.root}>
    {Object.keys(reactions).map((index) => {
      const reaction = reactions[index]
      const emoji = REACTIONS[reaction.emoji]

      return (
        <div
          key={index}
          style={{ left: `${reaction.left}%` }}
          className={classnames(styles.reaction, reaction.className)}
        >
          <Emoji text={emoji} />
        </div>
      )
    })}
  </div>
)

EmojiReactions.propTypes = {
  reactions: PropTypes.object.isRequired,
}

export default memo(EmojiReactions)
