import { useEffectOnce } from 'react-use'
import { useRecoilState } from 'recoil'
import Givecloud from 'givecloud'
import canMakePaymentState from '@/atoms/canMakePayment'

const useWalletPay = () => {
  const [canMakePayment, setCanMakePayment] = useRecoilState(canMakePaymentState)

  useEffectOnce(async () => {
    if (canMakePayment === void 0) {
      setCanMakePayment(await Givecloud.PaymentTypeGateway('wallet_pay')?.canMakePayment())
    }
  })

  return canMakePayment
}

export default useWalletPay
