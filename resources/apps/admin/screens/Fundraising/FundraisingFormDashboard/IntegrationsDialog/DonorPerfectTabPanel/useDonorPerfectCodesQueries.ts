import type { DPCode } from './DonorPerfectCommandInput'
import type { FundraisingFormId } from '@/types'
import { useQueries } from 'react-query'
import { useRecoilValue } from 'recoil'
import configState from '@/atoms/config'
import { createAxios } from '@/utilities/createAxios'
import { BASE_ADMIN_PATH, FUNDRAISING_FORMS_PATH } from '@/constants/pathConstants'

enum DPCODES_KEYS {
  GL_CODE = 'dpGlCodes',
  CAMPAIGN_CODE = 'dpCampaignCodes',
  SOLICIT_CODE = 'dpSolicitCodes',
  SUBSOLICIT_CODE = 'dpSubSolicitCodes',
}

interface CustomField {
  key: string
  field: string
  isEnabled: boolean
}

type Data = DPCode[]

interface EnabledQueries {
  dpGlCode: boolean
  dpCampaign: boolean
  dpSolicitCode: boolean
  dpSubSolicitCode: boolean
  customFieldQueries?: CustomField[]
}

const useDonorPerfectCodesQueries = (enabledQueries: EnabledQueries, id?: FundraisingFormId) => {
  const {
    dpGlCode: isDpGlCodeEnabled,
    dpCampaign: isDpCampaignEnabled,
    dpSolicitCode: isDpSolicitCodeEnabled,
    dpSubSolicitCode: isDpSubSolicitCodeEnabled,
    customFieldQueries,
  } = enabledQueries

  const { clientUrl } = useRecoilValue(configState)
  const baseURL = [clientUrl, BASE_ADMIN_PATH, '/donor/codes/'].join('')
  const glCodes = 'GL_CODE.json'
  const campaignCodes = 'CAMPAIGN.json'
  const solicitCodes = 'SOLICIT_CODE.json'
  const subSolicitCodes = 'SUB_SOLICIT_CODE.json'

  const { get } = createAxios({ baseURL, errorRedirect: `${FUNDRAISING_FORMS_PATH}/${id}` })

  const fetchCodes = async (filename: string) => {
    const { data } = await get<Data>(filename)
    return data
  }

  const staleTime = 30 * 10000

  const createCustomQuery = ({ field, isEnabled }: CustomField) => ({
    queryKey: [`${field}_CODES`],
    queryFn: () => fetchCodes(`${field}.json`),
    enabled: isEnabled,
    staleTime,
  })

  const customQueries = () => (customFieldQueries?.length ? customFieldQueries.map(createCustomQuery) : [])

  return useQueries([
    {
      queryKey: [DPCODES_KEYS.GL_CODE],
      queryFn: () => fetchCodes(glCodes),
      enabled: isDpGlCodeEnabled,
      staleTime,
    },
    {
      queryKey: [DPCODES_KEYS.CAMPAIGN_CODE],
      queryFn: () => fetchCodes(campaignCodes),
      enabled: isDpCampaignEnabled,
      staleTime,
    },
    {
      queryKey: [DPCODES_KEYS.SOLICIT_CODE],
      queryFn: () => fetchCodes(solicitCodes),
      enabled: isDpSolicitCodeEnabled,
      staleTime,
    },
    {
      queryKey: [DPCODES_KEYS.SUBSOLICIT_CODE],
      queryFn: () => fetchCodes(subSolicitCodes),
      enabled: isDpSubSolicitCodeEnabled,
      staleTime,
    },
    ...customQueries(),
  ])
}
export { useDonorPerfectCodesQueries }
