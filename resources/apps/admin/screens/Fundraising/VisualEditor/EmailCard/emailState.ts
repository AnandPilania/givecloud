import type { FundraisingForm } from '@/types'
import { atom, selector } from 'recoil'

interface EmailErrors {
  thankYouEmailMessage: string[]
}
export interface EmailState extends Pick<FundraisingForm, 'thankYouEmailMessage'> {
  errors?: EmailErrors
  touchedInputs?: Record<string, string>
}

export const emailState = atom<EmailState>({
  key: 'emailState',
  default: {
    thankYouEmailMessage: 'We are grateful for your support',
    errors: {
      thankYouEmailMessage: [],
    },
    touchedInputs: {},
  },
})

export const emailErrorState = selector({
  key: 'emailErrorState',
  get: ({ get }) => {
    const { errors, touchedInputs } = get(emailState)
    const isthankYouEmailMessageDirty = !!touchedInputs?.['thankYouEmailMessage']
    return isthankYouEmailMessageDirty && !!errors?.thankYouEmailMessage?.length
  },
})
