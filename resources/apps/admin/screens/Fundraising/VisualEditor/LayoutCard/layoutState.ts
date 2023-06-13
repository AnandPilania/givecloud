import type { FundraisingForm } from '@/types'
import { atom, selector } from 'recoil'
import configState from '@/atoms/config'

export interface LayoutState
  extends Pick<FundraisingForm, 'layout' | 'landingPageHeadline' | 'landingPageDescription' | 'backgroundImage'> {
  touchedInputs?: Record<string, string>
  errors?: Record<string, string[]>
}

const defaultLayoutState = selector({
  key: 'defaultLayoutState',
  get: ({ get }) => {
    const { isFundraisingFormsStandardLayoutEnabled } = get(configState)

    return {
      touchedInputs: {},
      errors: {},
      layout: isFundraisingFormsStandardLayoutEnabled ? 'standard' : 'simplified',
      landingPageHeadline: 'Donate Today',
      landingPageDescription: 'Join countless others in creating a meaningful impact.',
      backgroundImage: {
        id: '',
        full: '',
      },
    }
  },
})

export const layoutState = atom<LayoutState>({
  key: 'layoutState',
  default: defaultLayoutState,
})

export const layoutErrorState = selector({
  key: 'layoutErrorState',
  get: ({ get }) => {
    const { touchedInputs, errors } = get(layoutState)
    const isHeadlineInputDirty = !!touchedInputs?.['landingPageHeadline']
    const isDescriptionInputDirty = !!touchedInputs?.['landingPageDescription']
    const isHeadlineError = !!errors?.landingPageHeadline?.length && isHeadlineInputDirty
    const isDescriptionError = !!errors?.landingPageDescription?.length && isDescriptionInputDirty
    return isHeadlineError || isDescriptionError
  },
})
