import { atom, useRecoilValue } from 'recoil'
import getConfig from '@/utilities/config'

const { fundraising_experience: defaultFundraisingExperience } = getConfig()

const fundraisingExperienceState = atom({
  key: 'fundraisingExperience',
  default: defaultFundraisingExperience,
})

const useFundraisingExperienceState = () => {
  const fundraisingExperience = useRecoilValue(fundraisingExperienceState)

  return {
    fundraisingExperience,
  }
}

export { useFundraisingExperienceState }
