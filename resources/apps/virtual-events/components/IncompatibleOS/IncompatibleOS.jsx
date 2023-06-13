import { memo } from 'react'
import PropTypes from 'prop-types'
import classNames from 'classnames'
import { VIDEO_PROVIDERS, YOUTUBE, VIDEO_PROVIDER_LABELS } from '@/constants/videoProviderConstants'
import styles from '@/components/IncompatibleOS/IncompatibleOS.scss'

const IncompatibleOS = ({ themeStyle, themePrimaryColor, videoId, videoProvider }) => {
  const videolinkPrefix =
    videoProvider === YOUTUBE ? `https://www.youtube.com/watch?v=` : `https://vimeo.com/`
  const videolink = `${videolinkPrefix}${videoId}`

  return (
    <div className={classNames(styles.root, themeStyle === 'dark' ? styles.dark : styles.light)}>
      <div className={styles.heading}>Incompatible Browser</div>

      <p>Sorry, this browser will not work to watch this event here.</p>

      <p>If you can use a newer browser, please do so. If not, you can watch the event here:</p>

      <div>
        <button
          className={classNames(styles.watchOnProviderSiteButton, styles[themePrimaryColor])}
          href={videolink}
        >
          Watch on {VIDEO_PROVIDER_LABELS[videoProvider]}
        </button>
      </div>
    </div>
  )
}

IncompatibleOS.propTypes = {
  themeStyle: PropTypes.string.isRequired,
  themePrimaryColor: PropTypes.string.isRequired,
  videoId: PropTypes.string.isRequired,
  videoProvider: PropTypes.oneOf(VIDEO_PROVIDERS).isRequired,
}

export default memo(IncompatibleOS)
