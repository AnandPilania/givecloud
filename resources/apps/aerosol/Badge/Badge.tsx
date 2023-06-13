import type { FC, HTMLProps, ReactNode } from 'react'
import type { ThemeType } from '@/shared/constants/theme'
import classNames from 'classnames'
import { PRIMARY } from '@/shared/constants/theme'
import styles from './Badge.styles.scss'

export type BadgeThemes = ThemeType | 'gradient'

interface Props extends HTMLProps<HTMLDivElement> {
  children: ReactNode
  theme?: BadgeThemes
  invertTheme?: boolean
}

const Badge: FC<Props> = ({ theme = PRIMARY, invertTheme, children, className, ...rest }) => {
  const css = classNames(styles.root, styles[theme], invertTheme && styles.inverted, className)

  return (
    <div {...rest} className={css} aria-hidden='true'>
      {children}
    </div>
  )
}

Badge.defaultProps = {
  theme: PRIMARY,
  invertTheme: false,
}

export { Badge }
