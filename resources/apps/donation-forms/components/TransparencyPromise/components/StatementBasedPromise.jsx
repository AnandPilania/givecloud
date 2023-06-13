import { useRecoilValue } from 'recoil'
import configState from '@/atoms/config'

const CalculationBasedPromise = () => {
  const { transparency_promise: transparencyPromise } = useRecoilValue(configState)

  return <p>{transparencyPromise.statement}</p>
}

export default CalculationBasedPromise
