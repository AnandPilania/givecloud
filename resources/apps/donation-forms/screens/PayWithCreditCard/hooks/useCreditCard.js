import { useRecoilValue } from 'recoil'
import useErrorBag from '@/hooks/useErrorBag'
import formInputState from '@/atoms/formInput'

const useCreditCard = () => {
  const formInput = useRecoilValue(formInputState)
  const { errorBag } = useErrorBag()

  const usingCreditCard = formInput.payment_type === 'credit_card'
  const hasInvalidCreditCard = !!(errorBag.card_number || errorBag.card_exp || errorBag.card_cvv)

  return { usingCreditCard, hasInvalidCreditCard }
}

export default useCreditCard
