import getConfig from '@/utilities/config'
import { atom, selector, useRecoilState } from 'recoil'

interface Onboarding {
  userShowFundraisingPixelInstructions: boolean
}

export const OnBoardingState = atom<Onboarding>({
  key: 'onBoardingState',
  default: selector({
    key: 'onBoardingSelector',
    get: () => {
      const { userShowFundraisingPixelInstructions } = getConfig()
      return {
        userShowFundraisingPixelInstructions,
      }
    },
  }),
})

const useOnboardingState = () => {
  const [onBoardingState, setOnBoardingState] = useRecoilState(OnBoardingState)

  return {
    onBoardingState,
    setOnBoardingState,
  }
}

export { useOnboardingState }
