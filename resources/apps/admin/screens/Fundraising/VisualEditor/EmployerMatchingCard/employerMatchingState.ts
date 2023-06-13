import type { FundraisingForm } from '@/types'
import { atom } from 'recoil'

export type DoubleDonationState = Pick<FundraisingForm, 'doubleTheDonationConnected' | 'doubleTheDonationEnabled'>

export const doubleTheDonationState = atom<DoubleDonationState>({
  key: 'doubleTheDonationState',
  default: {
    doubleTheDonationConnected: false,
    doubleTheDonationEnabled: false,
  },
})
