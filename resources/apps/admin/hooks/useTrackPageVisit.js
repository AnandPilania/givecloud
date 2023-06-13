import { useEffect } from 'react'
import { useHistory } from 'react-router-dom'
import axios from 'axios'
import useApiUrl from '@/hooks/api/useApiUrl'

const useTrackPageView = () => {
  const apiUrl = useApiUrl()
  const history = useHistory()

  useEffect(() => {
    function trackPageVisit() {
      axios.post(`${apiUrl}/track-page-visit`, {
        path: window.location.pathname,
      })
    }

    trackPageVisit() // To track the first pageview upon load
    const unlisten = history?.listen(trackPageVisit) // To track the subsequent pageviews
    return unlisten
  }, [history, apiUrl])

  return null
}

export default useTrackPageView
