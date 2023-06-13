import type { DPCode } from '@/screens/Fundraising/FundraisingFormDashboard/IntegrationsDialog/DonorPerfectTabPanel/DonorPerfectCommandInput'
import { useQueries } from 'react-query'
import { useRecoilValue } from 'recoil'
import configState from '@/atoms/config'
import { createAxios } from '@/utilities/createAxios'
import { BASE_ADMIN_PATH } from '@/constants/pathConstants'

interface CustomFieldQuery {
  key: string
  field: string
  isEnabled: boolean
}

type Data = DPCode[]

interface EnabledQueries {
  customFieldQueries?: CustomFieldQuery[]
}

const useCustomDPCodesQueries = (enabledQueries: EnabledQueries) => {
  const { clientUrl } = useRecoilValue(configState)
  const baseURL = [clientUrl, BASE_ADMIN_PATH, '/donor/codes/'].join('')
  const { customFieldQueries } = enabledQueries

  const { get } = createAxios({ baseURL })

  const fetchCodes = async (filename: string) => {
    const { data } = await get<Data>(filename)
    return data
  }

  const staleTime = 30 * 10000

  const createCustomFieldQuery = ({ field, isEnabled }: CustomFieldQuery) => ({
    queryKey: [`${field}_CODES`],
    queryFn: () => fetchCodes(`${field}.json`),
    enabled: isEnabled,
    staleTime,
  })

  const customQueries = () => (customFieldQueries?.length ? customFieldQueries.map(createCustomFieldQuery) : [])

  return useQueries([...customQueries()])
}
export { useCustomDPCodesQueries }
