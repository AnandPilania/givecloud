import type { FundraisingForm } from '@/types'
import { atom, selector } from 'recoil'

interface UpsellErrors {
  upsellDescription: string[]
}
export interface UpsellState extends Pick<FundraisingForm, 'upsellEnabled' | 'upsellDescription'> {
  errors?: UpsellErrors
  touchedInputs?: Record<string, string>
}
export const upsellState = atom<UpsellState>({
  key: 'upsellState',
  default: {
    upsellEnabled: true,
    upsellDescription: 'Increase your impact as much as 12x.',
    errors: {
      upsellDescription: [],
    },
    touchedInputs: {},
  },
})

export const upsellErrorState = selector({
  key: 'upsellErrorState',
  get: ({ get }) => {
    const { errors, touchedInputs } = get(upsellState)
    const isUpsellDiscriptionDirty = !!touchedInputs?.['upsellDescription']
    return isUpsellDiscriptionDirty && !!errors?.upsellDescription?.length
  },
})
