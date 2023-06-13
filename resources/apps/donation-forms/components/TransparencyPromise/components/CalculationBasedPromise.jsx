import { useRecoilValue } from 'recoil'
import useCurrencyFormatter from '@/hooks/useCurrencyFormatter'
import configState from '@/atoms/config'
import formInputState from '@/atoms/formInput'

const CalculationBasedPromise = () => {
  const config = useRecoilValue(configState)
  const formInput = useRecoilValue(formInputState)

  const formatCurrency = useCurrencyFormatter({ abbreviate: true })

  const formatTransparencyPromiseAmount = (transparencyPromise) => {
    return formatCurrency((formInput.item.amt * transparencyPromise.percentage) / 100)
  }

  return (
    <>
      {config.transparency_promise.promises.map((transparencyPromise, index) => (
        <p key={index}>
          <strong>{formatTransparencyPromiseAmount(transparencyPromise)}</strong> {transparencyPromise.label}
        </p>
      ))}
    </>
  )
}

export default CalculationBasedPromise
