import type { FC, HTMLProps, ReactNode } from 'react'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import classNames from 'classnames'
import styles from './CarouselItem.styles.scss'

interface Props extends HTMLProps<HTMLDivElement> {
  isPaddingless?: boolean
  itemIndex?: number
  children?: ReactNode
}

const CarouselItem: FC<Props> = ({ children, isPaddingless, itemIndex, className, ...rest }) => {
  const { activeIndex } = useCarouselContext()

  const isVisible = activeIndex === itemIndex

  const css = classNames(
    styles.root,
    isPaddingless && styles.noPadding,
    isVisible ? styles.isVisible : styles.isHidden,
    className
  )

  return (
    <div {...rest} className={css} aria-hidden={!isVisible}>
      {children}
    </div>
  )
}

export { CarouselItem }
