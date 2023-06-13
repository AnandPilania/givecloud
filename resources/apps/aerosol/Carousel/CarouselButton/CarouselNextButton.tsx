import type { FC, MouseEvent } from 'react'
import type { CarouselButtonProps } from './CarouselButton'
import { forwardRef } from 'react'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import { CarouselButton } from './CarouselButton'

const CarouselNextButton: FC<CarouselButtonProps> = forwardRef(
  ({ children, isDisabled, isClean, onClick, ...rest }, ref) => {
    const { setActiveIndex, activeIndex, carouselItemsCount, isLooping } = useCarouselContext()

    const isLastCarouselItem = activeIndex === carouselItemsCount - 1

    const handleNextActiveIndex = (e: MouseEvent<HTMLButtonElement, globalThis.MouseEvent>) => {
      if (isLooping) {
        isLastCarouselItem ? setActiveIndex(carouselItemsCount - activeIndex - 1) : setActiveIndex(activeIndex + 1)
      }
      if (activeIndex < carouselItemsCount - 1) {
        setActiveIndex(activeIndex + 1)
      }
      onClick?.(e)
    }

    return (
      <CarouselButton
        {...rest}
        ref={ref}
        isClean={isClean}
        isDisabled={isDisabled || (isLastCarouselItem && !isLooping)}
        onClick={handleNextActiveIndex}
        aria-label='next item'
      >
        {children}
      </CarouselButton>
    )
  }
)

export { CarouselNextButton }
