import { useRecoilValue } from 'recoil'
import localeState from '@/atoms/locale'
import trans from '@/utilities/localization'
import { useCallback } from 'react'

const useLocalization = (prefix) => {
  const locale = useRecoilValue(localeState)

  prefix = prefix ? `${prefix}.` : ''

  return useCallback(
    (key, substitutions = {}) => {
      return trans(locale, `${prefix}${key}`, substitutions)
    },
    [locale, prefix]
  )
}

export default useLocalization
