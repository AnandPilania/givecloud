import type { FC } from 'react'
import type { CarouselButtonProps } from './CarouselButton'
import classNames from 'classnames'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import styles from './CarouselButton.styles.scss'

const CarouselLink: FC<CarouselButtonProps> = ({ children, index, indexToNavigate, className, ...rest }) => {
  const { setActiveIndex } = useCarouselContext()
  const css = classNames(styles.link, className)

  const handleClick = () => {
    if (indexToNavigate !== undefined) {
      setActiveIndex(indexToNavigate)
    }
  }

  return (
    <button {...rest} type='button' className={css} onClick={handleClick}>
      {children}
    </button>
  )
}

export { CarouselLink }
