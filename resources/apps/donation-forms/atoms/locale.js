import { atom } from 'recoil'
import Givecloud from 'givecloud'

const locale = atom({
  key: 'locale',
  default: Givecloud.config.locale.iso,
})

export default locale
