import type { AxiosResponse } from 'axios'
import type { FundraisingForm } from '@/types'
import type { UseMutationOptions } from 'react-query'
import { FUNDRAISING_FORM_KEYS } from '@/constants/queryKeys'
import { createAxios } from '@/utilities/createAxios'
import { useQueryClient, useMutation } from 'react-query'

const { FUNDRAISING_FORMS } = FUNDRAISING_FORM_KEYS

interface FundraisingFormData
  extends Omit<
    FundraisingForm,
    'isDefaultForm' | 'brandingLogo' | 'brandingMonthlyLogo' | 'socialPreviewImage' | 'backgroundImage' | 'id' | 'stats'
  > {
  brandingLogo?: string
  brandingMonthlyLogo?: string
  socialPreviewImage?: string
  backgroundImage?: string
}

interface Variables {
  fundraisingForm: FundraisingFormData
}

interface Error {
  message: string
}

interface Data {
  data: Omit<FundraisingForm, 'stats'>
}

type Response = AxiosResponse<Data>

type Options = UseMutationOptions<Response, Error, Variables>

const useCreateFundraisingFormMutation = (options?: Options) => {
  const { post } = createAxios()
  const queryClient = useQueryClient()

  const postForm = async (payload: FundraisingFormData) => await post<Data>(`donation-forms`, payload)

  return useMutation<Response, Error, Variables>(({ fundraisingForm }) => postForm(fundraisingForm), {
    ...options,
    onSuccess: () => queryClient.invalidateQueries([FUNDRAISING_FORMS]),
  })
}

export { useCreateFundraisingFormMutation }
