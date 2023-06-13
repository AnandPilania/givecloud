import type { UseMutationOptions } from 'react-query'
import { useMutation } from 'react-query'
import Givecloud from 'givecloud'
import { PeerToPeerCampaign } from '@/types'

interface Error {
  message: string
}

interface Campaign {
  fundraising_form_id: string
  fundraiser_type: string
  team_name?: string
  team_join_code?: string
  avatar_name: string
  goal_amount?: number
  currency_code?: string
}

type Options = UseMutationOptions<PeerToPeerCampaign, Error, Campaign>

const createPeerToPeerCampaign = async (payload: Campaign) =>
  await Givecloud.Account.PeerToPeerCampaigns.create(payload)

const useCreatePeerToPeerCampaignMutation = (options?: Options) => {
  return useMutation<PeerToPeerCampaign, Error, Campaign>((payload) => createPeerToPeerCampaign(payload), {
    ...options,
  })
}

export { useCreatePeerToPeerCampaignMutation }
