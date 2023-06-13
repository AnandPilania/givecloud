import { atom } from 'recoil'
import getConfig from '@/utilities/config'

const { fundraising_experience: defaultFundraisingExperience } = getConfig()

const fundraisingExperience = atom({
  key: 'fundraisingExperience',
  default: defaultFundraisingExperience,
})

export default fundraisingExperience
