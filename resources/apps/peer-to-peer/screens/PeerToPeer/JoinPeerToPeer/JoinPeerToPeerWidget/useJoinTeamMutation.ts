import type { UseMutationOptions } from 'react-query'
import type { PeerToPeerCampaign } from '@/types'
import { useMutation } from 'react-query'
import Givecloud from 'givecloud'

interface Error {
  message: string
}

type Options = UseMutationOptions<PeerToPeerCampaign, Error, Variables>

type Campaign = Pick<PeerToPeerCampaign, 'avatar_name' | 'team_join_code' | 'goal_amount' | 'team_goal_amount'>

interface Variables {
  id: string
  campaign: Campaign
}

const useJoinTeamMutation = (options?: Options) => {
  const joinTeam = async ({ id, campaign }: Variables) => await Givecloud.Account.PeerToPeerCampaigns.join(id, campaign)

  return useMutation<PeerToPeerCampaign, Error, Variables>((payload) => joinTeam(payload), { ...options })
}

export { useJoinTeamMutation }
