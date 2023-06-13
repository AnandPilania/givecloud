import { getThemeColour } from '@/shared/utilities'
import { createAxios } from '@/utilities/createAxios'
import { useRecoilState, atom, selector } from 'recoil'

const fetchBrandingData = async () => {
  const { get } = createAxios()
  const {
    data: { data: response },
  } = await get('settings/branding')

  return { ...response, orgPrimaryColor: getThemeColour(response?.orgPrimaryColor) }
}

export const brandingState = atom({
  key: 'orgBrandingState',
  default: selector({
    key: 'orgBrandingSelector',
    get: async () => {
      const response = await fetchBrandingData()
      return {
        ...response,
        errors: [],
      }
    },
  }),
})

const useBrandingSettingsState = () => {
  const [brandingValue, setBrandingValue] = useRecoilState(brandingState)

  return {
    brandingValue,
    setBrandingValue,
  }
}

export { useBrandingSettingsState }
