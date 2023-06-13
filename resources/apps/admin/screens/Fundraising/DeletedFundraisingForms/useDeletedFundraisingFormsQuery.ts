import type { UseQueryOptions } from 'react-query'
import type { FundraisingForm } from '@/types'
import { createAxios } from '@/utilities/createAxios'
import { useQuery } from 'react-query'
import { FUNDRAISING_DELETED_FORMS_PATH } from '@/constants/pathConstants'
import { FUNDRAISING_FORM_KEYS } from '@/constants/queryKeys'

const { DELETED_FUNDRAISING_FORMS } = FUNDRAISING_FORM_KEYS

interface Error {
  message: string
}

interface Data {
  data: FundraisingForm[]
}

type Options = UseQueryOptions<FundraisingForm[], Error>

export const useDeletedFundraisingFormsQuery = (options?: Options) => {
  const { get } = createAxios({ errorRedirect: FUNDRAISING_DELETED_FORMS_PATH })

  const fetchDeletedForms = async () => {
    const { data } = await get<Data>('/donation-forms?archived=1&include_stats=1')

    return data?.data
  }

  return useQuery<FundraisingForm[], Error>(DELETED_FUNDRAISING_FORMS, fetchDeletedForms, {
    ...options,
  })
}
