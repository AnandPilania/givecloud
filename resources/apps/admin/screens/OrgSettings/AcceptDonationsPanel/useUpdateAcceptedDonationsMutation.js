import { useMutation } from 'react-query'
import { createAxios } from '@/utilities/createAxios'

const useUpdateAcceptedDonationsMutation = (options = {}) => {
  const { patch } = createAxios()

  const updateAcceptedDonationsSettings = async (payload) => await patch('settings/accept-donations', payload)

  return useMutation((payload) => updateAcceptedDonationsSettings(payload), {
    ...options,
  })
}

export { useUpdateAcceptedDonationsMutation }
