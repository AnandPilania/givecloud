import { atom, useRecoilValue, useSetRecoilState } from 'recoil'
import getConfig from '@/utilities/config'

const { supporter: defaultSupporter } = getConfig()

const supporterState = atom({
  key: 'supporter',
  default: defaultSupporter,
})

const useSupporterState = () => {
  const setSupporter = useSetRecoilState(supporterState)
  const supporter = useRecoilValue(supporterState)
  const isAuthenticated = !!supporter?.email

  return {
    supporter,
    setSupporter,
    isAuthenticated,
  }
}

export { useSupporterState }
