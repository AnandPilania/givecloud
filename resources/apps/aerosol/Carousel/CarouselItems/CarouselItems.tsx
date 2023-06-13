import type { FC, HTMLProps, ReactNode } from 'react'
import { isValidElement, cloneElement, Children } from 'react'
import { CarouselItem } from '@/aerosol/Carousel/CarouselItem'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import styles from './CarouselItems.styles.scss'
import classNames from 'classnames'

const CarouselItemType = (<CarouselItem />).type

interface Props extends HTMLProps<HTMLDivElement> {
  children?: ReactNode
}

interface CarouselItemChild {
  itemIndex: number
}

const CarouselItems: FC<Props> = ({ children, className }) => {
  const { activeIndex } = useCarouselContext()

  return (
    <div className={classNames(styles.root, className)} style={{ transform: `translateX(-${activeIndex * 100}%)` }}>
      {Children.map(children, (child, itemIndex) =>
        isValidElement<CarouselItemChild>(child) && child.type === CarouselItemType
          ? cloneElement(child, { itemIndex })
          : child
      )}
    </div>
  )
}

export { CarouselItems }
