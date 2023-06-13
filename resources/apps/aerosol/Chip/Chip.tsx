import type { HTMLProps, ReactNode, FC } from 'react'
import type { ThemeType } from '@/shared/constants/theme'
import classnames from 'classnames'
import { PRIMARY } from '@/shared/constants/theme'
import styles from './Chip.styles.scss'

interface ChipProps<T = HTMLAnchorElement> extends HTMLProps<T> {
  theme?: ThemeType
  invertTheme?: boolean
  children: ReactNode
}

type Props = ChipProps<HTMLButtonElement | HTMLDivElement>

const Chip: FC<Props> = ({ theme = PRIMARY, onClick, invertTheme, children, href, target, className, ...rest }) => {
  const css = classnames(styles.root, styles[theme], invertTheme && styles.inverted, className)

  if (href) {
    return (
      <a {...(rest as HTMLProps<HTMLAnchorElement>)} className={css} href={href} target={target}>
        {children}
      </a>
    )
  }

  if (onClick) {
    return (
      <button {...(rest as HTMLProps<HTMLButtonElement>)} type='button' className={css} onClick={onClick}>
        {children}
      </button>
    )
  }

  return (
    <div {...(rest as HTMLProps<HTMLDivElement>)} className={css} data-testid='Chip'>
      {children}
    </div>
  )
}

export { Chip }
