import type { FC, HTMLProps, PropsWithChildren } from 'react'
import type { LinkProps } from 'react-router-dom'
import { Link } from 'react-router-dom'
import classNames from 'classnames'
import styles from './ClickableTile.styles.scss'

type ReactLinkProps = Partial<Pick<LinkProps, 'to'>>

type ClickableTileProps<T = HTMLAnchorElement> = PropsWithChildren<HTMLProps<T>> & ReactLinkProps

type Props = Omit<ClickableTileProps<HTMLButtonElement | HTMLAnchorElement>, 'ref'>

const ClickableTile: FC<Props> = ({ to, href, onClick, children, className, ...rest }) => {
  const css = classNames(styles.root, className)

  if (to) {
    return (
      <Link {...rest} to={to} className={css}>
        {children}
      </Link>
    )
  }

  if (href) {
    return (
      <a {...(rest as HTMLProps<HTMLAnchorElement>)} href={href} className={css}>
        {children}
      </a>
    )
  }

  return (
    <button {...(rest as HTMLProps<HTMLButtonElement>)} onClick={onClick} className={css} type='button'>
      {children}
    </button>
  )
}

export { ClickableTile }
