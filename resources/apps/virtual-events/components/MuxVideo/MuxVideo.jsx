import { memo, useRef } from 'react'
import PropTypes from 'prop-types'
import VolumeIcon from '@/components/Icons/VolumeIcon/VolumeIcon'
import usePlyr from '@/hooks/usePlyr'
import useHlsVideo from '@/hooks/useHlsVideo'
import styles from '@/components/MuxVideo/MuxVideo.scss'

const MuxVideo = ({ videoId }) => {
  const videoRef = useRef(null)
  const { handleUnmute, isMuted } = usePlyr(videoRef)
  const src = `https://stream.mux.com/${videoId}.m3u8`

  useHlsVideo(videoRef, src)

  return (
    <div className={styles.root}>
      <video ref={videoRef} controls crossOrigin='true' playsInline autoPlay />

      {isMuted && (
        <section>
          <button onClick={handleUnmute} type='button'>
            <VolumeIcon />
            Unmute Video
          </button>
        </section>
      )}
    </div>
  )
}

MuxVideo.propTypes = {
  videoId: PropTypes.string.isRequired,
}

export default memo(MuxVideo)
