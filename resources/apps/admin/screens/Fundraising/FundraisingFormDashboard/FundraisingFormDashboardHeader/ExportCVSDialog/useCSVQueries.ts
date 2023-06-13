import type { FundraisingFormId } from '@/types'
import { BASE_ADMIN_PATH } from '@/constants/pathConstants'
import getConfig from '@/utilities/config'
import { createAxios } from '@/utilities/createAxios'
import { useQueries } from 'react-query'

export enum CSV_KEYS {
  CONTRIBUTIONS = 'contributions',
  SUPPORTERS = 'supporters',
  PERFORMANCE = 'performance',
}

interface Options {
  id?: FundraisingFormId
  onSuccess: (key: CSV_KEYS, data: BlobPart) => void
}

const useCSVQueries = ({ id, onSuccess }: Options) => {
  const { clientUrl } = getConfig()
  const baseURL = [clientUrl, BASE_ADMIN_PATH].join('')
  const contributionsUrl = `/contributions.csv?df=${id}`
  const supportersUrl = `/supporters/export/all?donationForms=${id}`
  const performanceUrl = `/fundraising/forms/${id}/performance-summary.csv?days=90`

  const { get } = createAxios({
    baseURL,
  })

  const fetchCSVData = async (url: string) => {
    const { data } = await get<BlobPart>(url)
    return data
  }

  const staleTime = 1000 * 60 * 60 * 24 // 24hours

  return useQueries([
    {
      queryKey: [CSV_KEYS.CONTRIBUTIONS, id],
      queryFn: () => fetchCSVData(contributionsUrl),
      enabled: false,
      onSuccess: (data: BlobPart) => onSuccess(CSV_KEYS.CONTRIBUTIONS, data),
      staleTime,
    },
    {
      queryKey: [CSV_KEYS.SUPPORTERS, id],
      queryFn: () => fetchCSVData(supportersUrl),
      enabled: false,
      onSuccess: (data: BlobPart) => onSuccess(CSV_KEYS.SUPPORTERS, data),
      staleTime,
    },
    {
      queryKey: [CSV_KEYS.PERFORMANCE, id],
      queryFn: () => fetchCSVData(performanceUrl),
      enabled: false,
      onSuccess: (data: BlobPart) => onSuccess(CSV_KEYS.PERFORMANCE, data),
      staleTime,
    },
  ])
}

export { useCSVQueries }
