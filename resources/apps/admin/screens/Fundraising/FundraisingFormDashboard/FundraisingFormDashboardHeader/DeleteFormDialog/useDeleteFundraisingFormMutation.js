import { useMutation } from 'react-query'
import { createAxios } from '@/utilities/createAxios'
import { useQueryClient } from 'react-query'
import { FUNDRAISING_FORMS_PATH } from '@/constants/pathConstants'

const useDeleteFundraisingFormMutation = (id, options = {}) => {
  const { destroy } = createAxios({ errorRedirect: `${FUNDRAISING_FORMS_PATH}/${id}?deleteFundraisingForm` })
  const queryClient = useQueryClient()

  const destroyForm = async (id) => await destroy(`donation-forms/${id}`)

  return useMutation(() => destroyForm(id), {
    ...options,
    onSuccess: (_, id) =>
      queryClient.setQueryData('fundraising-forms', (forms) => forms.filter((form) => form.id !== id)),
  })
}

export { useDeleteFundraisingFormMutation }
