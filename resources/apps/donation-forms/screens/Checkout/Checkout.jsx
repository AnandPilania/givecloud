import { useRecoilState } from 'recoil'
import { useEffectOnce } from 'react-use'
import { AnimatePresence, useAnimation } from 'framer-motion'
import CompleteCheckout from '@/screens/CompleteCheckout/CompleteCheckout'
import PayWithCreditCard from '@/screens/PayWithCreditCard/PayWithCreditCard'
import PaymentStatus from '@/screens/PaymentStatus/PaymentStatus'
import checkoutScreenState from '@/atoms/checkoutScreen'
import Screen from '@/components/Screen/Screen'

const Checkout = () => {
  const [checkoutScreen, setCheckoutScreen] = useRecoilState(checkoutScreenState)

  const controls = {
    pay_with_credit_card: useAnimation(),
    complete_checkout: useAnimation(),
  }

  useEffectOnce(() => {
    setCheckoutScreen({
      action: 'PUSH',
      active: 'pay_with_credit_card',
    })

    controls.pay_with_credit_card.set('show')
    controls.complete_checkout.set('hide')
  })

  const navigateTo = (index, action = 'PUSH', method = 'start') => {
    setCheckoutScreen({
      action: action === 'PUSH' ? 'POP' : 'PUSH',
      active: index,
    })

    controls[checkoutScreen.active][method]('exit')
    controls[index][method]('show')
  }

  // don't judge... will do this properly when we're less
  // pressed for time and we can find a proper solution
  window.G_navigateTo = navigateTo

  return (
    <Screen includeTestMode={false}>
      <AnimatePresence>
        <PayWithCreditCard
          key='pay_with_credit_card'
          action={checkoutScreen.action}
          controls={controls.pay_with_credit_card}
          navigateTo={navigateTo}
        />
        <CompleteCheckout
          key='complete_checkout'
          action={checkoutScreen.action}
          controls={controls.complete_checkout}
        />
      </AnimatePresence>
      <PaymentStatus navigateTo={navigateTo} />
    </Screen>
  )
}

export default Checkout
