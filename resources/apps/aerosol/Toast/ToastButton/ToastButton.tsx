import type { FC, HTMLProps, MouseEvent } from 'react'
import type { TypeOptions } from 'react-toastify'
import type { ToastButtonProps } from '@/aerosol/Toast'
import classnames from 'classnames'
import { useHistory } from 'react-router-dom'
import styles from './ToastButton.styles.scss'

interface Props extends HTMLProps<HTMLButtonElement>, ToastButtonProps {
  theme?: TypeOptions
  isFullWidth?: boolean
}

const ToastButton: FC<Props> = ({
  theme = 'success',
  children,
  onClick,
  isFullWidth,
  className,
  to,
  'aria-label': ariaLabel,
}) => {
  const css = classnames(styles.root, styles[theme], isFullWidth && styles.fullWidth, className)
  const history = useHistory()

  const handleClick = (e: MouseEvent<HTMLButtonElement, globalThis.MouseEvent>) => {
    if (to) return history.push(to)
    if (onClick) return onClick(e)
    return null
  }

  return (
    <button aria-label={ariaLabel} type='button' onClick={handleClick} className={css}>
      {children}
    </button>
  )
}

ToastButton.defaultProps = {
  theme: 'success',
}

export { ToastButton }
