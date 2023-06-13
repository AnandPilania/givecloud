import type { FundraisingForm } from '@/types'
import type { UseQueryOptions } from 'react-query'
import { FUNDRAISING_FORM_KEYS } from '@/constants/queryKeys'
import { createAxios } from '@/utilities/createAxios'
import { useQuery } from 'react-query'

const { FUNDRAISING_FORMS } = FUNDRAISING_FORM_KEYS

export type FundraisingForms = FundraisingForm[]

interface Data {
  data: FundraisingForms
}

type Options = UseQueryOptions<FundraisingForms, Error>

interface Error {
  message: string
}

const useFundraisingFormsQuery = (options?: Options) => {
  const { get } = createAxios()

  const fetchForms = async () => {
    const { data } = await get<Data>('donation-forms?include_stats=1&include_trends=1')
    return data?.data
  }

  return useQuery<FundraisingForms, Error>(FUNDRAISING_FORMS, fetchForms, { staleTime: Infinity, ...options })
}

export { useFundraisingFormsQuery }
