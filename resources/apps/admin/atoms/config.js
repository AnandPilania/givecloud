import { selector } from 'recoil'
import getConfig from '@/utilities/config'

const config = selector({
  key: 'config',
  get: () => getConfig(),
})

export default config
