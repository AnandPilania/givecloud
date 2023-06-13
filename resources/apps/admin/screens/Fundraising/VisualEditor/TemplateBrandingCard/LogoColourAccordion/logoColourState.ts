import type { FundraisingForm } from '@/types'
import type { ColoursType } from '@/shared/constants/theme'
import { atom, selector } from 'recoil'
import { getThemeColour } from '@/shared/utilities'

interface BrandingColourErrors {
  colour: string[]
}

export interface BrandingState extends Pick<FundraisingForm, 'brandingLogo' | 'brandingMonthlyLogo'> {
  brandingColour: ColoursType
  errors: BrandingColourErrors
  touchedInputs?: Record<string, string>
}

export const fetchBrandingData = async () => {
  const url = `/jpanel/api/v1/donation-forms/global-settings`
  const response = await fetch(url)
  const { data } = await response?.json()

  return {
    brandingColour: getThemeColour(data?.org_primary_color),
    brandingLogo: {
      id: data?.org_logo?.id,
      full: data?.org_logo?.full,
    },
    brandingMonthlyLogo: {
      id: '',
      full: '',
    },
    errors: {
      colour: [],
    },
  }
}

const brandingSelector = selector<BrandingState>({
  key: 'brandingSelector',
  get: async () => await fetchBrandingData(),
  set: ({ set, get }, newState) => set(brandingState, { ...get(brandingState), ...newState }),
})

export const brandingState = atom<BrandingState>({
  key: 'brandingState',
  default: brandingSelector,
})

export const colourErrorState = selector({
  key: 'colourErrorState',
  get: ({ get }) => {
    const { errors } = get<BrandingState>(brandingState)
    return !!errors?.colour?.length
  },
})
