import { useMutation } from 'react-query'
import { createAxios } from '@/utilities/createAxios'

const useUpdateBrandingSettingsMutation = (options = {}) => {
  const { patch } = createAxios()

  const updateBrandingSettings = async (payload) => await patch('settings/branding', payload)

  return useMutation((payload) => updateBrandingSettings(payload), {
    ...options,
  })
}

export { useUpdateBrandingSettingsMutation }
