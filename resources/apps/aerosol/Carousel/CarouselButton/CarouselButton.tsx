import type { FC, HTMLProps, MouseEvent, ReactNode, Ref } from 'react'
import { PRIMARY, CUSTOM_THEME as CUSTOM } from '@/shared/constants/theme'
import { forwardRef } from 'react'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import classNames from 'classnames'
import styles from './CarouselButton.styles.scss'

type Theme = typeof PRIMARY | typeof CUSTOM

interface Props extends Omit<HTMLProps<HTMLButtonElement>, 'ref'> {
  index?: number
  indexToNavigate?: number
  isDisabled?: boolean
  isClean?: boolean
  isFullyRounded?: boolean
  children: ReactNode
  ref?: Ref<HTMLButtonElement>
  theme?: Theme
}

const CarouselButton: FC<Props> = forwardRef(
  (
    {
      children,
      index,
      indexToNavigate,
      isDisabled,
      isClean,
      isFullyRounded,
      onClick,
      className,
      theme = PRIMARY,
      ...rest
    },
    ref
  ) => {
    const { setActiveIndex, activeIndex } = useCarouselContext()

    const isSelected = activeIndex === (indexToNavigate || index)

    const css = classNames(
      styles.root,
      styles[theme],
      isDisabled && styles.disabled,
      isClean && styles.clean,
      isSelected && styles.selected,
      isFullyRounded && styles.rounded,
      className
    )

    const handleClick = (e: MouseEvent<HTMLButtonElement, globalThis.MouseEvent>) => {
      if (isDisabled) return null

      if (indexToNavigate !== undefined) {
        onClick?.(e)
        setActiveIndex(indexToNavigate)
      } else {
        onClick?.(e)
      }
    }

    return (
      <button {...rest} ref={ref} aria-disabled={isDisabled} type='button' className={css} onClick={handleClick}>
        {children}
      </button>
    )
  }
)

export { CarouselButton }
export type { Props as CarouselButtonProps }
