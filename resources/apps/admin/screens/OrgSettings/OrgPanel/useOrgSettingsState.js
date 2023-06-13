import { useRecoilState, atom, selector } from 'recoil'
import { createAxios } from '@/utilities/createAxios'

const fetchOrgSettingsData = async () => {
  const { get } = createAxios()
  const {
    data: { data: response },
  } = await get('settings/organization')

  return { ...response }
}

const orgState = atom({
  key: 'orgSettingsState',
  default: selector({
    key: 'orgSelector',
    get: async () => {
      try {
        return await fetchOrgSettingsData()
      } catch (err) {
        return {}
      }
    },
  }),
})

const useOrgSettingsState = () => {
  const [orgValue, setOrgValue] = useRecoilState(orgState)

  return {
    orgValue,
    setOrgValue,
  }
}

export { useOrgSettingsState }
