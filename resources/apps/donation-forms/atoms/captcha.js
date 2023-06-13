import { atom } from 'recoil'
import Givecloud from 'givecloud'

const captcha = atom({
  key: 'captcha',
  default: {
    type: Givecloud.config.captcha_type,
    site_key: Givecloud.config.recaptcha_site_key,
    required: Givecloud.config.requires_captcha,
    response: null,
    reset: null,
  },
})

export default captcha
