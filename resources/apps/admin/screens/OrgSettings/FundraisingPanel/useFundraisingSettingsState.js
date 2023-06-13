import { atom, selector, useRecoilState } from 'recoil'
import { createAxios } from '@/utilities/createAxios'

const fetchFundraisingSettingsData = async () => {
  const { get } = createAxios()
  const {
    data: { data: response },
  } = await get('settings/fundraising')

  const { orgOtherWaysToDonate, ...remainder } = response ?? {}

  return {
    ...remainder,
    orgOtherWaysToDonate: orgOtherWaysToDonate.length ? orgOtherWaysToDonate : [{ id: '1', label: '', link: '' }],
  }
}

const fundraisingState = atom({
  key: 'fundraisingSettingsState',
  default: selector({
    key: 'fundraisingSelector',
    get: async () => await fetchFundraisingSettingsData(),
  }),
})

const useFundraisingSettingsState = () => {
  const [fundraisingValue, setFundraisingValue] = useRecoilState(fundraisingState)

  return {
    fundraisingValue,
    setFundraisingValue,
  }
}

export { useFundraisingSettingsState }
