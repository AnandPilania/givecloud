import { useRecoilValue } from 'recoil'
import { timeAgo } from '@/utilities/date'
import localeState from '@/atoms/locale'

const useTimeAgo = () => {
  const locale = useRecoilValue(localeState)

  return (date) => timeAgo(locale, date)
}

export default useTimeAgo
