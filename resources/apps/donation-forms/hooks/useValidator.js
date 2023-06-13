import { useRecoilValue } from 'recoil'
import Validator from 'validatorjs'
import ValidatorMessages from 'validatorjs/src/messages'
import localeState from '@/atoms/locale'

import enUS from 'validatorjs/src/lang/en'
import esMX from 'validatorjs/src/lang/es'
import frCA from 'validatorjs/src/lang/fr'

const validatorMessages = {
  'en-US': enUS,
  'es-MX': esMX,
  'fr-CA': frCA,
}

const useValidator = () => {
  const locale = useRecoilValue(localeState)
  const language = locale.replace(/-[A-Z]{2}$/, '')

  return (data, rules, customMessages) => {
    const validator = new Validator(data, rules, customMessages)

    // this monkey patch is required to avoid issues with dynamic requires
    validator.messages = new ValidatorMessages(language, validatorMessages[locale])

    return validator
  }
}

export default useValidator
