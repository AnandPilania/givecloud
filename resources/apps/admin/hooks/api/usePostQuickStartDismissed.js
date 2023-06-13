import { useMutation } from 'react-query'
import axios from 'axios'
import useFeatureApiUrl from '@/hooks/api/useFeatureApiUrl'
import { QUICK_START_MENU_CLOSED_FEATURE } from '@/constants/featureConstants'

const usePostQuickStartDismissed = () => {
  const featureApiUrl = useFeatureApiUrl({ feature: QUICK_START_MENU_CLOSED_FEATURE })

  const postQuickStartDismissedRequest = async () => axios.post(featureApiUrl)

  const { mutate } = useMutation(postQuickStartDismissedRequest)

  return { mutate }
}

export default usePostQuickStartDismissed
