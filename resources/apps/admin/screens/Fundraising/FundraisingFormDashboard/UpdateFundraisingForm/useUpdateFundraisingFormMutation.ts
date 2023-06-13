import type { UseMutationOptions } from 'react-query'
import type { AxiosResponse } from 'axios'
import type { FundraisingForm, FundraisingFormId } from '@/types'
import { useQueryClient, useMutation } from 'react-query'
import { createAxios } from '@/utilities/createAxios'
import { FUNDRAISING_FORM_KEYS } from '@/constants/queryKeys'
import { FUNDRAISING_FORMS_PATH } from '@/constants/pathConstants'

const { FUNDRAISING_FORM, FUNDRAISING_FORMS } = FUNDRAISING_FORM_KEYS

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

const useUpdateFundraisingFormMutation = (id: FundraisingFormId, options?: Options) => {
  const queryClient = useQueryClient()
  const { patch } = createAxios({ errorRedirect: `${FUNDRAISING_FORMS_PATH}/${id}?form=updateFundraisingForm` })

  const updateForm = async ({ fundraisingForm }: Variables) =>
    await patch<FundraisingFormId, Response>(`donation-forms/${id}`, fundraisingForm)

  return useMutation<Response, Error, Variables>((payload) => updateForm(payload), {
    ...options,
    onSuccess: ({ data }) => {
      queryClient.invalidateQueries([FUNDRAISING_FORMS])
      queryClient.invalidateQueries([FUNDRAISING_FORM, data?.data?.id])
    },
  })
}

export { useUpdateFundraisingFormMutation }
