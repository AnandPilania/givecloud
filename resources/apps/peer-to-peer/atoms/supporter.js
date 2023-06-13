import { atom } from 'recoil'
import getConfig from '@/utilities/config'

const { supporter: defaultSupporter } = getConfig()

const supporter = atom({
  key: 'supporter',
  default: defaultSupporter,
})

export default supporter
