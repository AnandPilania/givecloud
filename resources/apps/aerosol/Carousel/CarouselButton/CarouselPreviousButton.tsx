import type { FC, MouseEvent } from 'react'
import type { CarouselButtonProps } from './CarouselButton'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import { CarouselButton } from './CarouselButton'

const CarouselPreviousButton: FC<CarouselButtonProps> = ({ children, isDisabled, onClick, ...rest }) => {
  const { setActiveIndex, activeIndex, carouselItemsCount, isLooping } = useCarouselContext()

  const isFirstCarouselItem = activeIndex === 0

  const handlePreviousActiveIndex = (e: MouseEvent<HTMLButtonElement, globalThis.MouseEvent>) => {
    if (isLooping) {
      isFirstCarouselItem ? setActiveIndex(carouselItemsCount - 1) : setActiveIndex(activeIndex - 1)
    }
    if (!isFirstCarouselItem) {
      setActiveIndex(activeIndex - 1)
    }
    onClick?.(e)
  }

  return (
    <CarouselButton
      {...rest}
      isDisabled={isDisabled || (isFirstCarouselItem && !isLooping)}
      onClick={handlePreviousActiveIndex}
      aria-label='previous item'
    >
      {children}
    </CarouselButton>
  )
}

export { CarouselPreviousButton }
