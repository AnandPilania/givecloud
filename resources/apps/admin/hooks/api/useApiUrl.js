import { useRecoilValue } from 'recoil'
import configState from '@/atoms/config'
import { API_V1 } from '@/constants/apiConstants'

const useApiUrl = () => {
  const { clientUrl } = useRecoilValue(configState)

  return [clientUrl, API_V1].join('/')
}

export default useApiUrl
