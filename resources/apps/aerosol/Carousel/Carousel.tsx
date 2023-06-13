import type { FC, ReactNode } from 'react'
import { useState, Children, isValidElement, useMemo, useEffect } from 'react'
import { CarouselContext } from './CarouselContext'
import { CarouselItems } from './CarouselItems'
import styles from './Carousel.styles.scss'

interface Props {
  isLooping?: boolean
  initialIndex?: number
  children: ReactNode
  name: string
}

const CarouselItemsType = (<CarouselItems />).type

const Carousel: FC<Props> = ({ isLooping, initialIndex = 0, children, name }) => {
  const [activeIndex, setActiveIndex] = useState(initialIndex)

  useEffect(() => {
    if (initialIndex === activeIndex) return
    setActiveIndex(initialIndex)
  }, [initialIndex])

  const getNumberOfCarouselItems = () => {
    let numberOfCarouselItems = 0
    Children.forEach(children, (child) => {
      if (isValidElement(child) && child.type === CarouselItemsType) {
        numberOfCarouselItems = Children.toArray(child.props.children).filter((child) => !!child).length
      }
    })
    return numberOfCarouselItems
  }

  const carouselItemsCount = useMemo(getNumberOfCarouselItems, [children])

  return (
    <section className={styles.carousel} aria-label={`${name} carousel`}>
      <CarouselContext.Provider
        value={{
          isLooping,
          activeIndex,
          setActiveIndex,
          carouselItemsCount,
        }}
      >
        {children}
      </CarouselContext.Provider>
    </section>
  )
}

Carousel.defaultProps = {
  initialIndex: 0,
  isLooping: false,
}

export { Carousel }
