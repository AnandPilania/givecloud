import { useRecoilValue } from 'recoil'
import formatCurrency from '@/utilities/currency'
import localeState from '@/atoms/locale'
import formInputState from '@/atoms/formInput'

const useCurrencyFormatter = (options = {}) => {
  const locale = useRecoilValue(localeState)
  const formInput = useRecoilValue(formInputState)

  const formattingOptions = {
    locale,
    autoFractionDigits: true,
    showCurrencyCode: false,
    showCurrencySymbol: true,
    ...options,
  }

  return (amount, options = {}) => {
    const currencyCode = options.currencyCode || formattingOptions.currencyCode || formInput.currency_code

    return formatCurrency(amount, currencyCode, { ...formattingOptions, ...options })
  }
}

export default useCurrencyFormatter
