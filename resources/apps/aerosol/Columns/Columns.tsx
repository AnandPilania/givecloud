import type { HTMLProps, ReactNode, FC } from 'react'
import classNames from 'classnames'
import styles from './Columns.styles.scss'

interface Props extends HTMLProps<HTMLDivElement> {
  children: ReactNode
  isMarginless?: boolean
  isWrapping?: boolean
  isWrappingReverse?: boolean
  isStackingOnMobile?: boolean
  isResponsive?: boolean
  isMarginPreserved?: boolean
}

const Columns: FC<Props> = ({
  children,
  isMarginless,
  isWrapping,
  isWrappingReverse,
  isStackingOnMobile,
  isResponsive,
  isMarginPreserved,
  className,
  ...rest
}) => {
  const rowStyles = isResponsive ? styles.responsiveRows : styles.rows

  const columnsStyles = classNames(
    styles.root,
    !isMarginPreserved && styles.lastOfTypeMargin,
    rowStyles,
    isWrappingReverse && styles.reverseWrap,
    isStackingOnMobile ? rowStyles : styles.rowOnMobile,
    isMarginless && styles.isMarginless,
    isWrapping && styles.wrap,
    className
  )

  return (
    <div {...rest} className={columnsStyles}>
      {children}
    </div>
  )
}

Columns.defaultProps = {
  isMarginPreserved: false,
  isResponsive: true,
  isStackingOnMobile: true,
  isMarginless: false,
  isWrapping: false,
  isWrappingReverse: false,
}

export { Columns }
export { Props as ColumnsProps }
