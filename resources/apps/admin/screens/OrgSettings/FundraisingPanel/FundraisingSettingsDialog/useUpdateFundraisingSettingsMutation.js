import { createAxios } from '@/utilities/createAxios'
import { useMutation } from 'react-query'

const useUpdateFundraisingSettingsMutation = (options = {}) => {
  const { patch } = createAxios()

  const updateFundraisingSettings = async (payload) => await patch('settings/fundraising', payload)

  return useMutation((payload) => updateFundraisingSettings(payload), { ...options })
}

export { useUpdateFundraisingSettingsMutation }
