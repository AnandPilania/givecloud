import type { FundraisingForm as FundraisingFormType, FundraisingFormId } from '@/types'
import type { AxiosResponse } from 'axios'
import type { UseMutationOptions } from 'react-query'
import { createAxios } from '@/utilities/createAxios'
import { useMutation, useQueryClient } from 'react-query'
import { FUNDRAISING_FORM_KEYS } from '@/constants/queryKeys'

const { FUNDRAISING_FORM, FUNDRAISING_FORMS } = FUNDRAISING_FORM_KEYS

type FundraisingForm = Omit<FundraisingFormType, 'stats'>

interface Variables {
  id: FundraisingFormId
}

interface Data {
  data: FundraisingForm
}

interface Error {
  message: string
}

type Response = AxiosResponse<Data>

type Options = UseMutationOptions<Response, Error, Variables>

const updateForm = (form: FundraisingFormType, defaultFormId: FundraisingFormId) =>
  form.id === defaultFormId ? { ...form, isDefaultForm: true } : { ...form, isDefaultForm: false }

const updateForms = (forms: FundraisingFormType[], defaultFormId: FundraisingFormId) =>
  forms?.map((form) => updateForm(form, defaultFormId))

const useDefaultFundraisingFormMutation = (options?: Options) => {
  const queryClient = useQueryClient()
  const { post } = createAxios()

  const setFormToDefault = async (id: FundraisingFormId) => await post<Data>(`donation-forms/${id}/make-default`)

  return useMutation<Response, Error, Variables>(({ id }) => setFormToDefault(id), {
    ...options,
    onSuccess: (_, { id }) => {
      queryClient.setQueryData<FundraisingFormType[]>(FUNDRAISING_FORMS, (forms) => updateForms(forms!, id))
      queryClient.setQueriesData<FundraisingFormType>(FUNDRAISING_FORM, (form) => updateForm(form!, id))
    },
  })
}

export { useDefaultFundraisingFormMutation }
