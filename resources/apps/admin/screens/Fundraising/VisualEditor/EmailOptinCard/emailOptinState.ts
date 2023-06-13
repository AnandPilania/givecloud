import type { FundraisingForm } from '@/types'
import { atom, selector } from 'recoil'

interface EmailOptErrors {
  emailOptinDescription: string[]
}

export interface EmailOptInState extends Pick<FundraisingForm, 'emailOptinDescription' | 'emailOptinEnabled'> {
  errors?: EmailOptErrors
  touchedInputs?: Record<string, string>
}

export const emailOptInState = atom<EmailOptInState>({
  key: 'emailOptInState',
  default: {
    emailOptinDescription: 'Can we send you updates on the impact YOUR donation is having?',
    emailOptinEnabled: true,
    errors: {
      emailOptinDescription: [],
    },
    touchedInputs: {},
  },
})

export const emailOptinErrorState = selector({
  key: 'emailOptinErrorState',
  get: ({ get }) => {
    const { errors, touchedInputs } = get(emailOptInState)
    const isUpsellDiscriptionDirty = !!touchedInputs?.['emailOptinDescription']
    return isUpsellDiscriptionDirty && !!errors?.emailOptinDescription?.length
  },
})
