import type { FC, HTMLProps, MouseEvent, ReactNode, Ref } from 'react'
import type { IconDefinition } from '@fortawesome/pro-regular-svg-icons'
import type { LinkProps } from 'react-router-dom'
import type { ThemeType } from '@/shared/constants/theme'
import { PRIMARY } from '@/shared/constants/theme'
import { Link } from 'react-router-dom'
import { forwardRef } from 'react'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSpinner } from '@fortawesome/pro-regular-svg-icons'
import styles from './Button.styles.scss'

export type ButtonSizes = 'small' | 'medium'
type ButtonType = 'button' | 'submit' | 'reset' | undefined
type ReactLinkProps = Partial<Pick<LinkProps, 'to'>>

interface ButtonProps<T = HTMLButtonElement> extends HTMLProps<T>, ReactLinkProps {
  theme: ThemeType
  isLoading: boolean
  isDisabled: boolean
  icon: IconDefinition
  isClean: boolean
  isOutlined: boolean
  isFullWidth: boolean
}
interface Props extends Partial<Omit<ButtonProps<HTMLButtonElement | HTMLAnchorElement>, 'size' | 'type' | 'ref'>> {
  children: ReactNode
  type?: ButtonType
  size?: ButtonSizes
  ref?: Ref<HTMLButtonElement>
}

const Button: FC<Props> = forwardRef(
  (
    {
      isLoading,
      isDisabled,
      theme = PRIMARY,
      icon,
      size = 'medium',
      isClean,
      children,
      isOutlined,
      to,
      isFullWidth,
      className,
      type,
      href,
      'aria-label': ariaLabel,
      onClick,
      ...rest
    },
    ref
  ) => {
    const buttonStyles = isDisabled
      ? classnames(styles.disabled, isClean && styles.clean, isOutlined && styles.outlined)
      : classnames(styles[theme], isClean && styles.clean, isOutlined && styles.outlined)

    const css = classnames(
      styles.root,
      isFullWidth && styles.fullWidth,
      !!children && styles.hasChildren,
      buttonStyles,
      styles[size],
      className
    )

    const handleClick = (e: MouseEvent<HTMLButtonElement, globalThis.MouseEvent>) => {
      if (isDisabled) return null
      return onClick?.(e)
    }

    if (to) {
      return (
        <Link aria-label={ariaLabel} to={to} className={css}>
          {children}
        </Link>
      )
    }

    if (href) {
      const linkProps = isDisabled
        ? { role: 'link', 'aria-disabled': true, tabIndex: 0 }
        : { href, ...(rest as HTMLProps<HTMLAnchorElement>) }

      return (
        <a {...linkProps} aria-label={ariaLabel} className={css}>
          {children}
        </a>
      )
    }

    const getDisabledProps = () => {
      if (isLoading) {
        return {
          disabled: isLoading,
        }
      }
      return {
        'aria-disabled': isDisabled,
      }
    }

    return (
      <button
        {...(rest as HTMLProps<HTMLButtonElement>)}
        aria-label={ariaLabel}
        type={type}
        onClick={handleClick}
        ref={ref}
        {...getDisabledProps()}
        className={css}
      >
        <span className={classnames(isLoading ? 'opacity-0' : styles.button)}>
          {!!icon && <FontAwesomeIcon title={icon.iconName} className={styles.icon} icon={icon} />}
          {children}
        </span>
        <div role='status' className={classnames(isLoading ? styles.spinner : 'hidden')}>
          <FontAwesomeIcon icon={faSpinner} spin />
          <span aria-hidden={!isLoading} className='sr-only'>
            loading
          </span>
        </div>
      </button>
    )
  }
)

Button.displayName = 'Button'

Button.defaultProps = {
  size: 'medium',
  theme: PRIMARY,
  type: 'button',
}

export { Button }
export type { Props as ButtonProps }
