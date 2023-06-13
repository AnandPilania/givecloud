import type { FundraisingForm, FundraisingFormId } from '@/types'
import type { UseMutationOptions } from 'react-query'
import type { AxiosResponse } from 'axios'
import { createAxios } from '@/utilities/createAxios'
import { useMutation, useQueryClient } from 'react-query'
import { FUNDRAISING_FORM_KEYS } from '@/constants/queryKeys'
import { FUNDRAISING_DELETED_FORMS_PATH } from '@/constants/pathConstants'

const { FUNDRAISING_FORMS, DELETED_FUNDRAISING_FORMS } = FUNDRAISING_FORM_KEYS

interface Error {
  message: string
}

interface Data {
  data: FundraisingForm
}

type Response = AxiosResponse<Data>

type Options = UseMutationOptions<Response, Error, FundraisingFormId>

const useRestoreFundraisingFormMutation = (options?: Options) => {
  const { post } = createAxios({ errorRedirect: FUNDRAISING_DELETED_FORMS_PATH })
  const queryClient = useQueryClient()

  const restoreForm = async (id: FundraisingFormId) => await post(`/donation-forms/${id}/restore`)
  const updateDeletedForms = (forms: FundraisingForm[], restoredFormId: FundraisingFormId) =>
    forms.filter((form) => form.id !== restoredFormId)

  return useMutation((id) => restoreForm(id), {
    ...options,
    onSuccess: (_, id) => {
      if (queryClient.getQueryData(FUNDRAISING_FORMS)) {
        const deletedForm = queryClient
          .getQueryData<FundraisingForm[]>(DELETED_FUNDRAISING_FORMS)
          ?.find((form) => form.id === id)

        queryClient.setQueryData(FUNDRAISING_FORMS, ([first, ...remainder]) => [
          { ...first },
          { ...deletedForm },
          ...remainder,
        ])
      }
      queryClient.setQueryData<FundraisingForm[]>(DELETED_FUNDRAISING_FORMS, (deletedForms) =>
        updateDeletedForms(deletedForms ?? [], id)
      )
    },
  })
}

export { useRestoreFundraisingFormMutation }
