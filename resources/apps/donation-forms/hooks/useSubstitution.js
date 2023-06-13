import useLocalizationValue from '@/hooks/useLocalizationValue'
import { substitute } from '@/utilities/string'

const useSubstitution = (key) => {
  const getLocalizationValue = useLocalizationValue(key)

  return (key, value, substitutions = {}) => {
    const dangerouslySetInnerHTML = !!key.match(/_html$/)

    return substitute(value || getLocalizationValue(key), substitutions, dangerouslySetInnerHTML)
  }
}

export default useSubstitution
