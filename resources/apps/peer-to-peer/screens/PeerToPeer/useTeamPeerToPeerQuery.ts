import type { PeerToPeerCampaign } from '@/types'
import type { UseQueryOptions } from 'react-query'
import { useQuery } from 'react-query'
import Givecloud from 'givecloud'

interface Error {
  message: string
}

interface Options {
  options?: UseQueryOptions<PeerToPeerCampaign, Error>
  id?: string
}

const useTeamPeerToPeerQuery = ({ id, options }: Options) => {
  const fetchTeamPeerToPeer = async (payload?: string) => await Givecloud.PeerToPeerCampaigns.get(payload)

  return useQuery<PeerToPeerCampaign, Error>(['TeamPeerToPeer', id], () => fetchTeamPeerToPeer(id), {
    enabled: !!id,
    staleTime: Infinity,
    ...options,
  })
}

export { useTeamPeerToPeerQuery }
