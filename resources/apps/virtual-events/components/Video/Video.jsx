import { memo } from 'react'
import PropTypes from 'prop-types'
import { VIDEO_PROVIDERS, YOUTUBE, VIMEO, MUX } from '@/constants/videoProviderConstants'
import MuxVideo from '@/components/MuxVideo/MuxVideo'
import MuxPreStreamMessage from '@/components/MuxPreStreamMessage/MuxPreStreamMessage'
import styles from '@/components/Video/Video.scss'

const Video = ({
  themeStyle,
  videoProvider,
  videoId,
  liveStreamStatus,
  prestreamMessageLine1,
  prestreamMessageLine2,
}) => (
  <div className={styles.root}>
    <div className={styles.iframeContainer}>
      {videoProvider === YOUTUBE && (
        <iframe
          src={`https://www.youtube.com/embed/${videoId}?autoplay=1`}
          frameBorder='0'
          allowFullScreen
          allow='autoplay; encrypted-media; fullscreen'
        />
      )}

      {videoProvider === VIMEO && (
        <iframe
          src={`https://player.vimeo.com/video/${videoId}`}
          frameBorder='0'
          allow='autoplay; fullscreen'
          allowFullScreen
        />
      )}
      {videoProvider === MUX && (
        <>
          {liveStreamStatus === 'idle' && (
            <MuxPreStreamMessage
              themeStyle={themeStyle}
              prestreamMessageLine1={prestreamMessageLine1}
              prestreamMessageLine2={prestreamMessageLine2}
            />
          )}
          {liveStreamStatus !== 'idle' && <MuxVideo videoId={videoId} />}
        </>
      )}
    </div>
  </div>
)

Video.propTypes = {
  themeStyle: PropTypes.string.isRequired,
  videoProvider: PropTypes.oneOf(VIDEO_PROVIDERS).isRequired,
  videoId: PropTypes.string.isRequired,
  liveStreamStatus: PropTypes.string,
  prestreamMessageLine1: PropTypes.string,
  prestreamMessageLine2: PropTypes.string,
}

export default memo(Video)
