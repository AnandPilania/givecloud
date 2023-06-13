import type { FundraisingForm } from '@/types'
import { atom } from 'recoil'

export type ThankYouState = Pick<FundraisingForm, 'thankYouOnscreenMessage'>

export const thankYouState = atom<ThankYouState>({
  key: 'thankYouState',
  default: {
    thankYouOnscreenMessage: 'Your generosity is making an impact.',
  },
})
