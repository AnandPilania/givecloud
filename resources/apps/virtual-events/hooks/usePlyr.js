import { useState, useCallback } from 'react'
import { useEffectOnce } from 'react-use'
import Plyr from 'plyr'

const usePlyr = (videoRef) => {
  const [player, setPlayer] = useState(null)
  const [isPlaying, setIsPlaying] = useState(false)
  const [volume, setVolume] = useState(0)

  const onStartPlaying = useCallback(() => {
    setIsPlaying(true)
  }, [])

  const onStopPlaying = useCallback(() => {
    setIsPlaying(false)
  }, [])

  const onVolumeChange = useCallback((event) => {
    setVolume(event.detail.plyr.volume)
  }, [])

  const handleUnmute = useCallback(() => {
    player.volume = 1
  }, [player])

  useEffectOnce(() => {
    const player = new Plyr(videoRef.current, { autoplay: true, muted: true })

    player.on('playing', onStartPlaying)
    player.on('pause', onStopPlaying)
    player.on('volumechange', onVolumeChange)

    setPlayer(player)
    setVolume(player.volume)

    return () => {
      if (player) {
        player.off('playing', onStartPlaying)
        player.off('pause', onStopPlaying)
        player.off('volumechange', onVolumeChange)
        player.destroy()
      }
    }
  })

  return {
    isPlaying,
    isMuted: volume === 0,
    handleUnmute,
  }
}

export default usePlyr
