import type { Dispatch, SetStateAction } from 'react'
import { createContext, useContext } from 'react'

interface CarouselContextData {
  isLooping?: boolean
  activeIndex: number
  setActiveIndex: Dispatch<SetStateAction<number>>
  carouselItemsCount: number
}
const CarouselContext = createContext<CarouselContextData | null>(null)

const useCarouselContext = () => {
  const context = useContext(CarouselContext)
  if (context === null) throw new Error('useCarouselContext is not being used within a provider')
  return context
}

export { CarouselContext, useCarouselContext }
