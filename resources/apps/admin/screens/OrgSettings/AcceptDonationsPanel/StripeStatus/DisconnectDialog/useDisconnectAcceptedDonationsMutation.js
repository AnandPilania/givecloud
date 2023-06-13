import { useMutation } from 'react-query'
import { createAxios } from '@/utilities/createAxios'

const useDisconnectAcceptedDonationsMutation = (options = {}) => {
  const { patch } = createAxios()

  const disconnectAcceptedDonationsSettings = async (payload) =>
    await patch('settings/accept-donations/disconnect', payload)

  return useMutation((payload) => disconnectAcceptedDonationsSettings(payload), {
    ...options,
  })
}

export { useDisconnectAcceptedDonationsMutation }
