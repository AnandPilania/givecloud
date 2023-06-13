import { createAxios } from '@/utilities/createAxios'
import { useRecoilState, atom, selector } from 'recoil'

const fetchDonationData = async () => {
  const { get } = createAxios()
  const {
    data: { data: response },
  } = await get('settings/accept-donations')

  return { ...response }
}

export const acceptedDonationsState = atom({
  key: 'acceptedDonationsState',
  default: selector({
    key: 'acceptedDonationSelector',
    get: async () => await fetchDonationData(),
  }),
})

const useAcceptedDonationSettingsState = () => {
  const [acceptedDonationsValue, setAcceptedDonationsValue] = useRecoilState(acceptedDonationsState)

  return {
    acceptedDonationsValue,
    setAcceptedDonationsValue,
  }
}

export { useAcceptedDonationSettingsState }
