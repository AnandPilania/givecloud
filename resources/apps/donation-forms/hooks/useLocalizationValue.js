import { useRecoilValue } from 'recoil'
import localeState from '@/atoms/locale'
import { getLocalizationValue } from '@/utilities/localization'

const useLocalizationValue = (prefix) => {
  const locale = useRecoilValue(localeState)

  prefix = prefix ? `${prefix}.` : ''

  return (key, substitutions) => getLocalizationValue(locale, `${prefix}${key}`, substitutions)
}

export default useLocalizationValue
