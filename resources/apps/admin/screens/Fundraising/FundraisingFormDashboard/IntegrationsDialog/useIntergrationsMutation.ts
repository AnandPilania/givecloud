import type { UseMutationOptions } from 'react-query'
import type { AxiosResponse } from 'axios'
import type { IntegrationsState } from './integrationsState'
import type { FundraisingForm, FundraisingFormId } from '@/types'
import { FUNDRAISING_FORM_KEYS } from '@/constants/queryKeys'
import { FUNDRAISING_FORMS_PATH } from '@/constants/pathConstants'
import { useQueryClient, useMutation } from 'react-query'
import { createAxios } from '@/utilities/createAxios'

const { FUNDRAISING_FORM } = FUNDRAISING_FORM_KEYS

interface Data {
  data: FundraisingForm
}

type Response = AxiosResponse<Data>

interface Error {
  message: string
}

interface Variables {
  id?: string
  payload: IntegrationsState
}

type Options = UseMutationOptions<Response, Error, Variables>

const useIntegrationsMutation = (id?: FundraisingFormId, options?: Options) => {
  const queryClient = useQueryClient()
  const { patch } = createAxios({ errorRedirect: `${FUNDRAISING_FORMS_PATH}/${id}?integrations` })

  const updateIntegrations = async ({ payload }: Variables) =>
    await patch<FundraisingFormId, Response>(`donation-forms/${id}/integrations`, payload)

  return useMutation<Response, Error, Variables>((payload) => updateIntegrations(payload), {
    ...options,
    onSuccess: (_, { id }) => queryClient.invalidateQueries([FUNDRAISING_FORM, id]),
  })
}

export { useIntegrationsMutation }
