import { memo } from 'react'
import PropTypes from 'prop-types'
import { VIDEO_PROVIDERS, YOUTUBE, VIMEO } from '@/constants/videoProviderConstants'
import styles from '@/components/Comments/Comments.scss'

const Comments = ({ themeStyle, videoProvider, videoId, chatId, domain }) => (
  <div className={styles.root}>
    {videoProvider === YOUTUBE && (
      <iframe
        src={`https://www.youtube.com/live_chat?v=${videoId}&embed_domain=${domain}${
          themeStyle === 'dark' ? '&dark_theme=1' : ''
        }`}
        frameBorder='0'
        allow='autoplay; encrypted-media'
      />
    )}

    {videoProvider === VIMEO && (
      <iframe
        src={`https://vimeo.com/live-chat/${chatId}`}
        width='100%'
        height='100%'
        frameBorder='0'
      />
    )}
  </div>
)

Comments.propTypes = {
  themeStyle: PropTypes.string.isRequired,
  videoProvider: PropTypes.oneOf(VIDEO_PROVIDERS).isRequired,
  videoId: PropTypes.string.isRequired,
  chatId: PropTypes.string.isRequired,
  domain: PropTypes.string.isRequired,
}

export default memo(Comments)
