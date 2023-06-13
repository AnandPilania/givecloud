import { useRecoilValue } from 'recoil'
import moment from 'moment-timezone'
import configState from '@/atoms/config'

const useTimeOfDay = () => {
  const { timezone = '' } = useRecoilValue(configState)

  const now = timezone ? moment().tz(timezone) : moment()
  const currentHour = now.format('H')

  let timeOfDay = 'Morning'
  if (currentHour >= 12) timeOfDay = 'Afternoon'
  if (currentHour >= 17) timeOfDay = 'Evening'

  return timeOfDay
}

export default useTimeOfDay
