import { useRecoilValue } from 'recoil'
import currencyState from '@/atoms/currency'

const useCurrencySymbol = () => {
  return useRecoilValue(currencyState).symbol
}

export default useCurrencySymbol
