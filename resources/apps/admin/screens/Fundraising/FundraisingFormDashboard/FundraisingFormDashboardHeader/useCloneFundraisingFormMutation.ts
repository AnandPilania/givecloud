import type { FundraisingForm as FundraisingFormType, FundraisingFormId } from '@/types'
import type { UseMutationOptions } from 'react-query'
import { useMutation } from 'react-query'
import { createAxios } from '@/utilities/createAxios'
import { FUNDRAISING_FORMS_PATH } from '@/constants/pathConstants'

type FundraisingForm = Omit<FundraisingFormType, 'stats'>

interface Error {
  message: string
}

interface Variables {
  id: FundraisingFormId
}

interface Data {
  data: FundraisingForm
}

type Options = UseMutationOptions<FundraisingForm, Error, Variables>

const useCloneFundraisingFormMutation = (id?: FundraisingFormId, options?: Options) => {
  const { post } = createAxios({ errorRedirect: `${FUNDRAISING_FORMS_PATH}/${id}` })

  const cloneForm = async () => {
    const { data } = await post<Data>(`donation-forms/${id}/replicate`)
    return data?.data
  }

  return useMutation<FundraisingForm, Error, Variables>(() => cloneForm(), { ...options })
}

export { useCloneFundraisingFormMutation }
