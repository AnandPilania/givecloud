import type { FundraisingForm, FundraisingFormId } from '@/types'
import type { AxiosResponse } from 'axios'
import type { UseQueryOptions } from 'react-query'
import { useQuery, useQueryClient } from 'react-query'
import { createAxios } from '@/utilities/createAxios'
import { FUNDRAISING_FORM_KEYS } from '@/constants/queryKeys'
import { FUNDRAISING_FORMS_PATH } from '@/constants/pathConstants'

const { FUNDRAISING_FORM, FUNDRAISING_FORMS } = FUNDRAISING_FORM_KEYS

interface Data {
  data: AxiosResponse<FundraisingForm>
}

interface Error {
  message: string
}

interface Options {
  options?: UseQueryOptions<FundraisingForm, Error>
}

const useFundraisingFormWithStatsQuery = (id: FundraisingFormId, options?: Options) => {
  const queryClient = useQueryClient()
  const { get } = createAxios({ errorRedirect: `${FUNDRAISING_FORMS_PATH}/${id}` })

  const fetchForm = async () => {
    const { data } = await get<FundraisingFormId, Data>(`donation-forms/${id}?include_stats=1&include_trends=1`)
    return data?.data
  }
  return useQuery<FundraisingForm, Error>([FUNDRAISING_FORM, id], () => fetchForm(), {
    ...options,
    staleTime: 30 * 1000,
    initialData: () =>
      queryClient.getQueryData<FundraisingForm[]>(FUNDRAISING_FORMS)?.find(({ id: formId }) => formId === id),
  })
}

export { useFundraisingFormWithStatsQuery }
