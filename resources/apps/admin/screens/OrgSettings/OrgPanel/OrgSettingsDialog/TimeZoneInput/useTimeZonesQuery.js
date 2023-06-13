import { useQuery } from 'react-query'
import { createAxios } from '@/utilities/createAxios'

const useTimeZonesQuery = (options = {}) => {
  const baseURL = '/gc-json/v1/services/locale/timezones'
  const { get } = createAxios({
    baseURL,
  })

  const fetchTimeZones = async () => {
    const { data } = await get()

    return data
  }

  return useQuery('timeZones', fetchTimeZones, { ...options })
}

export { useTimeZonesQuery }
