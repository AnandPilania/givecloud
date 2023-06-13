import { useEffect } from 'react'
import Hls from 'hls.js'

const useHlsVideo = (videoRef, src) => {
  useEffect(() => {
    let hls

    if (videoRef.current) {
      const video = videoRef.current

      if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = src
      } else if (Hls.isSupported()) {
        hls = new Hls()
        hls.loadSource(src)
        hls.attachMedia(video)
      } else {
        console.error(`This is a legacy browser that doesn't support MSE`)
      }
    }

    return () => {
      if (hls) {
        hls.destroy()
      }
    }
  }, [videoRef, src])
}

export default useHlsVideo
