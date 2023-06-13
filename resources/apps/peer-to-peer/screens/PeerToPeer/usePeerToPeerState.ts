import type { PeerToPeerCampaign } from '@/types'
import { atom, useRecoilState } from 'recoil'
import getConfig from '@/utilities/config'

const {
  fundraising_experience: {
    local_currency: { code: currencyCode },
  },
  supporter: defaultSupporter,
} = getConfig()

interface TeamPeerToPeer {
  name: string
  joinCode: string
  shortUrl: string
  campaignId: string
  goalAmount: number
  teamMemberGoalAmount: number
  members: PeerToPeerCampaign[]
  amountRaised: null | number
}

interface PersonalPeerToPeer {
  goalAmount: number
  firstName?: string
}

interface PeerToPeerState {
  supporterInitials: string
  id?: string
  fundraiserType: string
  socialAvatar: string
  avatarName: string
  currencyCode: string
  team: TeamPeerToPeer
  personal: PersonalPeerToPeer
  shareLinks: Record<string, string>
}

const peerToPeerState = atom<PeerToPeerState>({
  key: 'peerToPeerState',
  default: {
    fundraiserType: 'personal',
    supporterInitials: '',
    socialAvatar: defaultSupporter?.avatar,
    avatarName: 'nature',
    currencyCode: currencyCode,
    team: {
      name: '',
      joinCode: '',
      campaignId: '',
      goalAmount: 2500,
      teamMemberGoalAmount: 500,
      members: [],
      shortUrl: '',
      amountRaised: 0,
    },
    personal: {
      firstName: '',
      goalAmount: 500,
    },

    shareLinks: {},
  },
})
const usePeerToPeerState = () => {
  const [peerToPeerValue, setPeerToPeerState] = useRecoilState(peerToPeerState)
  const { personal, team } = peerToPeerValue

  return {
    personal,
    team,
    peerToPeerValue,
    setPeerToPeerState,
  }
}

export { usePeerToPeerState }
