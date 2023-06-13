import type { FC, HTMLProps, ReactNode } from 'react'
import type { LinkProps } from 'react-router-dom'
import type { BoxProps } from '@/aerosol/Box'
import type { IconDefinition } from '@fortawesome/pro-regular-svg-icons'
import { Link } from 'react-router-dom'
import classNames from 'classnames'
import { Box } from '@/aerosol/Box'
import { ClickableBoxIcon } from './ClickableBoxIcon'
import styles from './ClickableBox.styles.scss'

type IconPlacement = 'top' | 'center' | 'bottom' | 'static'
type ReactLinkProps = Pick<LinkProps, 'to'>

interface ClickableBoxProps<T = HTMLAnchorElement> extends HTMLProps<T>, ReactLinkProps, Partial<BoxProps> {
  isCustomizable?: boolean
  icon?: IconDefinition
  iconPlacement?: IconPlacement
  children?: ReactNode
  dataTestId?: string
}

type Props = Omit<ClickableBoxProps<HTMLButtonElement | HTMLAnchorElement>, 'ref'>

const ClickableBox: FC<Props> = ({
  to,
  href,
  onClick,
  children,
  isOverflowVisible,
  isPaddingless,
  isFullscreen,
  isReducedPadding,
  isFullHeight,
  isCustomizable,
  icon,
  iconPlacement = 'center',
  isMarginless,
  dataTestId,
  ...rest
}) => {
  const { className, 'aria-label': ariaLabel, ...remainder } = rest
  const css = classNames(styles.root, isFullHeight && 'h-full', isMarginless && styles.noMargin, className)

  const renderContent = () =>
    isCustomizable ? (
      children
    ) : (
      <div className='pr-16 h-full'>
        {children}
        <ClickableBoxIcon icon={icon} placement={iconPlacement} />
      </div>
    )

  if (to) {
    return (
      <Link aria-label={ariaLabel} to={to} className={css} data-testid={dataTestId}>
        <Box
          isFullHeight={isFullHeight}
          isOverflowVisible={isOverflowVisible}
          isPaddingless={isPaddingless}
          isReducedPadding={isReducedPadding}
          isFullscreen={isFullscreen}
          className={styles.box}
          isMarginless
        >
          {renderContent()}
        </Box>
      </Link>
    )
  }

  if (href) {
    return (
      <a
        {...(remainder as HTMLProps<HTMLAnchorElement>)}
        aria-label={ariaLabel}
        href={href}
        className={css}
        data-testid={dataTestId}
      >
        <Box
          isFullHeight={isFullHeight}
          isOverflowVisible={isOverflowVisible}
          isPaddingless={isPaddingless}
          isReducedPadding={isReducedPadding}
          isFullscreen={isFullscreen}
          className={styles.box}
          isMarginless
        >
          {renderContent()}
        </Box>
      </a>
    )
  }

  return (
    <button
      {...(remainder as HTMLProps<HTMLButtonElement>)}
      aria-label={ariaLabel}
      type='button'
      onClick={onClick}
      className={css}
      data-testid={dataTestId}
    >
      <Box
        isFullHeight={isFullHeight}
        isOverflowVisible={isOverflowVisible}
        isPaddingless={isPaddingless}
        isReducedPadding={isReducedPadding}
        isFullscreen={isFullscreen}
        className={styles.box}
        isMarginless
      >
        {renderContent()}
      </Box>
    </button>
  )
}

ClickableBox.defaultProps = {
  isMarginless: false,
  isOverflowVisible: false,
  isPaddingless: false,
  isFullscreen: false,
  isReducedPadding: false,
  isFullHeight: false,
  isCustomizable: false,
  iconPlacement: 'center',
}

export { ClickableBox }
