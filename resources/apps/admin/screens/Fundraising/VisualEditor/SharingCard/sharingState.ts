import type { FundraisingForm } from '@/types'
import { atom, selector } from 'recoil'

export interface SharingState
  extends Pick<FundraisingForm, 'socialLinkTitle' | 'socialLinkDescription' | 'socialPreviewImage'> {
  touchedInputs?: Record<string, string>
  errors?: Record<string, string[]>
}

export const sharingState = atom<SharingState>({
  key: 'sharingState',
  default: {
    socialLinkTitle: 'Donate',
    socialLinkDescription: 'Your generosity makes a difference!',
    touchedInputs: {},
    errors: {},
    socialPreviewImage: {
      id: '',
      full: '',
    },
  },
})

export const sharingErrorState = selector({
  key: 'sharingErrorState',
  get: ({ get }) => {
    const { touchedInputs, errors } = get(sharingState)
    const isSocialLinkTitleInputDirty = !!touchedInputs?.['socialLinkTitle']
    const isSocialLinkDescriptionInputDirty = !!touchedInputs?.['socialLinkDescription']
    const isSocialLinkTitleError = !!errors?.socialLinkTitle?.length && isSocialLinkTitleInputDirty
    const isSocialLinkDescriptionError = !!errors?.socialLinkDescription?.length && isSocialLinkDescriptionInputDirty
    return isSocialLinkTitleError || isSocialLinkDescriptionError
  },
})
