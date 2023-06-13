import { useMutation } from 'react-query'
import { createAxios } from '@/utilities/createAxios'

const useUpdateOrgSettingsMutation = (options = {}) => {
  const { patch } = createAxios()

  const updateOrgSettings = async (payload) => await patch('settings/organization', payload)

  return useMutation((payload) => updateOrgSettings(payload), {
    ...options,
  })
}

export { useUpdateOrgSettingsMutation }
