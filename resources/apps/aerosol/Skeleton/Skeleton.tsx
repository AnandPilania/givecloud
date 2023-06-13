import type { FC, HTMLProps } from 'react'
import classNames from 'classnames'
import styles from './Skeleton.styles.scss'

type SkeletonSizes = 'small' | 'medium' | 'large' | 'full'

interface Props extends HTMLProps<HTMLDivElement> {
  width: SkeletonSizes
  height: SkeletonSizes
  isFullyRounded: boolean
  isMarginless: boolean
}

const Skeleton: FC<Partial<Props>> = ({
  width = 'small',
  height = 'small',
  isFullyRounded,
  isMarginless,
  className,
  ...rest
}) => {
  const widthStyles = classNames(styles.width, styles[`width--${width}`], styles.height, styles[`height--${height}`])
  const roundedStyles = classNames(styles.rounded, styles[`rounded--${width}`])
  const css = classNames(
    styles.root,
    !isMarginless && styles.margin,
    isFullyRounded ? roundedStyles : widthStyles,
    className
  )

  return <div data-testid='skeleton' className={css} {...rest} />
}

Skeleton.defaultProps = {
  width: 'small',
  height: 'small',
}

export { Skeleton }
