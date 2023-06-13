import { useRecoilValue } from 'recoil'
import { getCurrencySymbolPlacement } from '@/utilities/currency'
import localeState from '@/atoms/locale'
import currencyState from '@/atoms/currency'

const useCurrencySymbolPlacement = () => {
  const locale = useRecoilValue(localeState)
  const currency = useRecoilValue(currencyState)

  return getCurrencySymbolPlacement(locale, currency.code)
}

export default useCurrencySymbolPlacement
